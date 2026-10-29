<?php

declare(strict_types=1);

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\MarkdownConverter;

use App\Models\User\UserModel;

class Parser
{
    /**
     * Общий список расширений CommonMark (включая GFM-плюшки).
     */
    public static function environment(array $config = []): Environment
    {
        $env = new Environment($config);
        $env->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $env->addExtension(new AutolinkExtension());
        $env->addExtension(new FootnoteExtension());
        $env->addExtension(new SmartPunctExtension());
        $env->addExtension(new StrikethroughExtension());
        $env->addExtension(new TableExtension());
        $env->addExtension(new TaskListExtension());
        $env->addExtension(new ExternalLinkExtension());
        $env->addExtension(new DisallowedRawHtmlExtension());

        return $env;
    }

    public static function parse(string $content, string $type = 'text', bool $preview = false)
    {
        $content = str_replace('{cut}', '', $content);

        // Авто-ссылки [[Термин]] и [[Текст|Термин]] → резолвим до конвертации в /путь/slug.html
        $redLinks = [];
        if ($type !== 'mini') {
            list($content, $redLinks) = self::wikiLinks($content, $preview);
        }

        // Подпись "#тег" → ссылка на /topic/... (до парсинга)
        $content = self::topicLinks($content);

        // Блоки :::{.класс}…::: → placeholder (рендерим внутренность отдельно)
        $fencedDivs = [];
        $content = self::fencedDivs($content, $fencedDivs, $preview);

        // Мини-сниппеты: убираем сноски
        if ($type == 'mini') {
            $content = preg_replace('/^\[\^\d+\]:[^\r\n]*/mi', '', $content);
            $content = preg_replace('/\[\^\d+\]/', '', $content);
        }

        $converter = new MarkdownConverter(self::environment());

        $text = $converter->convert($content)->getContent();

        // Восстанавливаем fenced div'ы (после CommonMark, т.к. он экранирует HTML)
        if ($fencedDivs) {
            $text = strtr($text, $fencedDivs);
        }

        // Восстанавливаем красные ссылки
        if ($type !== 'mini' && $redLinks) {
            $text = strtr($text, $redLinks);
        }

        // Проставляем id заголовкам
        if ($type !== 'mini') {
            $text = self::addHeadingAnchors($text);
        }

        // В предпросмотре перемещаем внутренние ссылки на превью
        if ($preview) {
            $text = self::rewriteInternalLinks($text);
        }

        return $text;
    }

    /**
     * Блоки :::{.класс} … ::: → <div class="класс">…</div>.
     * Внутренность рендерится отдельным CommonMark-проходом, результат
     * подставляется placeholder'ом (чтобы CommonMark не экранировал HTML).
     */
    public static function fencedDivs(string $content, array &$fencedDivs, bool $preview = false): string
    {
        $admonitionIcons = [
            'note'    => 'ℹ️',
            'tip'     => '💡',
            'warning' => '⚠️',
            'danger'  => '🚨',
            'success' => '✅',
        ];

        return preg_replace_callback(
            '/^:::\s*\{?(\.?[a-zA-Z0-9_\-]+)\}?\s*$(.*?)^:::\s*$/ms',
            function ($m) use (&$fencedDivs, $preview, $admonitionIcons) {
                $class = ltrim(trim($m[1]), '.');
                $innerMarkdown = $m[2] ?? '';

                $inner = self::parseInner($innerMarkdown, $preview);

                // Адмонишен с иконкой
                $attrs = '';
                $dataIcon = $admonitionIcons[$class] ?? null;
                if ($dataIcon !== null) {
                    $class = 'admonition ' . $class;
                    $attrs .= ' data-icon="' . $dataIcon . '"';
                }

                $marker = '<!--WDIV' . count($fencedDivs) . '-->';
                $fencedDivs[$marker] = '<div class="' . $class . '"' . $attrs . '>' . "\n" . $inner . "\n" . '</div>';

                return $marker . "\n\n";
            },
            $content
        );
    }

    // Рендер вложенного markdown для ::{}-блоков (без рекурсии вики-ссылок, чтобы не зациклиться)
    protected static function parseInner(string $markdown, bool $preview = false): string
    {
        $converter = new MarkdownConverter(self::environment());
        $html = $converter->convert($markdown)->getContent();

        return trim($html);
    }

    /**
     * Перенос #тег → <a href="/topic/тег" class="green">#тег</a>
     * Поддерживает латиницу и кириллицу. До парсинга (простым регэкспом).
     */
    public static function topicLinks(string $content): string
    {
        return preg_replace_callback(
            '/(?<![A-Za-zА-Яа-я0-9_])#([A-Za-zА-Яа-я][A-Za-zА-Яа-я0-9_]*)/u',
            function ($m) {
                $tag = $m[1];
                $slug = mb_strtolower(urlencode($tag));
                return '<a href="/topic/' . $slug . '" class="green">#' . $tag . '</a>';
            },
            $content
        );
    }

    /**
     * Переписывает внутренние ссылки вида "/path/slug.html" на
     * "/mod/admin/preview/path/slug.html" — чтобы в админке можно было
     * ходить между статьями по внутренним ссылкам.
     */
    public static function rewriteInternalLinks(string $html): string
    {
        return preg_replace_callback(
            '/(href|src)="(\/[^"\s]*\.html)"/i',
            function ($m) {
                $url = $m[2];
                // Не трогаем те, что уже под preview
                if (str_starts_with($url, '/mod/admin/preview/')) {
                    return $m[0];
                }
                return $m[1] . '="' . rtrim(config('general', 'url'), '/') . '/mod/admin/preview' . $url . '"';
            },
            $html
        );
    }

    /**
     * Добавляет id = slug заголовкам h2/h3, если его ещё нет.
     * Slug строится так же, как в toc.php (Slugify), чтобы якоря совпадали.
     */
    public static function addHeadingAnchors(string $html): string
    {
        $slugify = new \Cocur\Slugify\Slugify();

        return preg_replace_callback(
            '/<(h2|h3)([^>]*)>(.*?)<\/\1>/si',
            function ($m) use ($slugify) {
                $tag  = $m[1];
                $attr = $m[2];
                $body = $m[3];

                // Уже есть id — не трогаем
                if (preg_match('/\bid=/i', $attr)) {
                    return $m[0];
                }

                $text = trim(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $slug = $slugify->slugify($text);

                if ($slug === '') {
                    return $m[0];
                }

                return '<' . $tag . $attr . ' id="' . $slug . '">' . $body . '</' . $tag . '>';
            },
            $html
        );
    }

    public static function miniature($markdown)
    {
		$pattern = '/!\[(.*?)\]\((.*?)\)/'; // Ищет ![]()

		if (preg_match_all($pattern, $markdown, $matches)) {

			foreach ($matches[0] as $match) {
				// return htmlspecialchars($match) . "\n"; // Выводит ![]()
			}

             return $matches[2][0]; 

		}
       return;
    }

    // TODO: Let's check the simple version for now.
    public static function cut($text, $length = 800)
    {
        $charset = 'UTF-8';
        $beforeCut = $text;
        $afterCut = false;

        if (preg_match("#^(.*){cut([^}]*+)}(.*)$#Usi", $text, $match)) {
            $beforeCut  = $match[1];
            $afterCut   = $match[3];
        }

        if (!$afterCut) {
            $beforeCut = self::fragment($text, $length);
        }

        $button = false;
        if ($afterCut || mb_strlen($text, $charset) > $length) {
            $button = true;
        }

        return ['content' => $beforeCut, 'button' => $button];
    }

    // Content management
    public static function noHTML(string $content, int $lenght = 150)
    {
        $converter = new MarkdownConverter(self::environment());
        $text = $converter->convert($content)->getContent();

        $content = str_replace(["\r\n", "\r", "\n", "#"], ' ', $text);

        $str =  str_replace(['&gt;', '{cut}'], '', strip_tags($content));

        // Вики-ссылки [[Термин]] / [[Текст|Термин]] → оставляем чистый текст без скобок
        $str = preg_replace('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\]/', '$1', $str);

        return self::fragment($str, $lenght);
    }

    public static function fragment(string $text, int $lenght = 150, string $charset = 'UTF-8'): string
    {
        if (mb_strlen($text, $charset) >= $lenght) {
            $wrap = wordwrap($text, $lenght, '~');
            $ret = mb_strpos($wrap, '~', 0, $charset);

            return  mb_substr($wrap, 0, (int)$ret, $charset) . '...';
        }

        if (empty($text)) $text = '...';

        return $text;
    }

    /**
     * Авто-ссылки [[Термин]] / [[Текст|Термин]].
     *
     * Возвращает [контент, массив маркер=>html] для красных ссылок (вставляются после convert,
     * т.к. CommonMark в safe-режиме экранирует инлайн-HTML).
     *
     * - [[Дзогчен]]               → нечёткий поиск; 1 результат → обычная ссылка markdown
     * - [[Текст|Дзогчен]]         → то же, но с кастомным текстом
     * - [[Текст|/путь/slug.html]] → явный путь (точная адресация)
     * - Не найдено / несколько    → в админке/превью красная ссылка на создание, иначе — как есть
     */
    public static function wikiLinks(string $content, bool $preview = false): array
    {
        $redLinks = [];

        $resolved = preg_replace_callback(
            '/\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/u',
            function ($m) use ($preview, &$redLinks) {
                $text   = trim((string)($m[1] ?? ''));
                $target = trim((string)($m[2] ?? $m[1] ?? ''));

                if ($text === '' || $target === '') {
                    return $m[0];
                }

                // 1) Явный путь: [[текст|/путь/slug.html]] (точная адресация)
                if (str_starts_with($target, '/') && str_ends_with($target, '.html')) {
                    return '[' . $text . '](' . $target . ')';
                }

                // 2) Резолв по термину
                $found = \App\Models\ItemModel::findByTerm($target);
                if ($found && isset($found[0])) {
                    $bestRank = $found[0]['_rank'] ?? 2;
                    if ($bestRank < 2) {
                        $ties = 0;
                        foreach ($found as $r) {
                            if (($r['_rank'] ?? 2) === $bestRank) {
                                $ties++;
                            }
                        }
                        if ($ties === 1) {
                            $row  = $found[0];
                            $path = Html::facets_puth($row['facet_list'] ?? '', 'category');
                            if ($path !== '') {
                                return '[' . $text . '](' . '/' . trim($path, '/') . '/' . $row['item_slug'] . '.html)';
                            }
                        }
                    }
                }

                // 3) Не найдено / неоднозначно / частичное вхождение:
                //    в админке и превью — красная ссылка на создание статьи.
                if ($preview) {
                    $marker = "\x1AWIKIRED" . count($redLinks) . "\x1A";
                    $createUrl = rtrim(config('general', 'url'), '/') . '/mod/admin/add/item?title=' . rawurlencode($target);
                    $redLinks[$marker] = '<a href="' . $createUrl . '" class="red-link">' . htmlspecialchars($text, ENT_QUOTES) . '</a>';
                    return $marker;
                }

                return $m[0];
            },
            $content
        );

        return [$resolved, $redLinks];
    }

    // Слагифицировать целевой термин так же, как при создании факта (AddItemController::getSlug)
    public static function wikiSlug(string $target): string
    {
        $slugify = new \Cocur\Slugify\Slugify();

        return substr((string)$slugify->slugify($target), 0, 90);
    }

}
