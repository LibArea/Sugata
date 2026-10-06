<?php

declare(strict_types=1);

use Djot\DjotConverter;
use Djot\SafeMode;
use Djot\Node\Inline\Link;
use Djot\Node\Inline\Text;
use Djot\Event\RenderEvent;
use Djot\Node\Block\Div;

use Djot\Extension\AutolinkExtension;
use Djot\Extension\ExternalLinksExtension;
use Djot\Extension\SmartQuotesExtension;
use Djot\Extension\MentionsExtension;

use App\Models\User\UserModel;

class Parser
{
    public static function parse(string $content, string $type)
    {
        $content = str_replace('{cut}', '', $content);
		
		if ($type == 'mini') {
          // В превью сноски не нужны: убираем определения сносок полностью,
          // затем маркеры в тексте (сначала определения — иначе паттерн не совпадёт).
          $content = preg_replace('/^\[\^\d+\]:[^\r\n]*/mi', '', $content);
          $content = preg_replace('/\[\^\d+\]/', '', $content);
		}

        // https://github.com/php-collective/djot-php/tree/master
		$converter = new DjotConverter(
			safeMode: SafeMode::strict(),
			significantNewlines: true,
		);

        self::reminders($converter);

        self::topic($converter);

        $text = $converter
            ->addExtension(new AutolinkExtension())
            ->addExtension(new ExternalLinksExtension())
            ->addExtension(new SmartQuotesExtension(locale: config('general', 'lang')))
            ->addExtension(new MentionsExtension(urlTemplate: '/@{username}', cssClass: 'green',))
            ->convert($content);

        // Проставляем id заголовкам h2/h3 — для якорей в оглавлении
        if ($type !== 'mini') {
            $text = self::addHeadingAnchors($text);
        }

        return $text;
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

    public static function reminders($converter)
    {
        $admonitionIcons = [
            'note' => 'ℹ️',
            'tip' => '💡',
            'warning' => '⚠️',
            'danger' => '🚨',
            'success' => '✅',
        ];

        $converter->on('render.div', function (RenderEvent $event) use ($admonitionIcons): void {
            $div = $event->getNode();
            if (!$div instanceof Div) {
                return;
            }

            $class = $div->getAttribute('class') ?? '';
            foreach ($admonitionIcons as $type => $icon) {
                if (str_contains($class, $type)) {
                    $div->setAttribute('class', 'admonition ' . $class);
                    $div->setAttribute('data-icon', $icon);

                    return;
                }
            }
        });
    }

    public static function topic($converter)
    {
        $parser = $converter->getParser()->getInlineParser();
        $parser->addInlinePattern('/#([a-zA-Z][a-zA-Z0-9_]*)/', function ($match, $groups, $p) {
            $tag = $groups[1];
            $link = new Link('/topic/' . strtolower($tag));
            $link->appendChild(new Text('#' . $tag));
            $link->setAttribute('class', 'green');
            return $link;
        });
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
        $converter = new DjotConverter(safeMode: SafeMode::strict());
        $text = $converter->convert($content);

        $content = str_replace(["\r\n", "\r", "\n", "#"], ' ', $text);

        $str =  str_replace(['&gt;', '{cut}'], '', strip_tags($content));

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

}
