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
    public static function text(string $content, string $type)
    {
        return self::parse($content);
    }

    public static function parse(string $content)
    {
        $content = str_replace('{cut}', '', $content);
        $content = str_replace('[^1]', '', $content);

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

        return $text;
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
