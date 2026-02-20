<?php

declare(strict_types=1);

use App\Models\ParserModel;
use App\Bootstrap\Services\User\UserData;

use Michelf\Markdown;
use Michelf\MarkdownExtra;

class Parser
{
    // Content management (Parsedown, Typograf)
    public static function text(string $content, string $type)
    {
        $text = self::parse($content);
        $text = self::details($text);
		
        return self::facets($text);
    }

    public static function parse(string $content)
    {
		$content = str_replace('{cut}', '', $content);
		
 		// https://michelf.ca/projects/php-markdown/
		$text = MarkdownExtra::defaultTransform($content);

        if (UserData::getUserLang() === 'ru') {
            return self::typograf($text);
        }

        return $text;
    }

    public static function typograf(string $text)
    {
        $t = new \Akh\Typograf\Typograf();

        /* $simpleRule = new class extends \Akh\Typograf\Rule\AbstractRule {
            public $name = 'Пример замены';
            protected $sort = 1000;
            public function handler(string $text): string
            {
                return str_replace('agouti.ru', '<a href="https://libarea.ru">libarea.ru</a>', $text);
            }
        };

        $t->addRule($simpleRule); */

        // https://github.com/akhx/typograf/blob/master/docs/RULES.md
        $t->disableRule('Nbsp\*');
        $t->disableRule('Space\*');
        $t->disableRule('Html\*');

        return $t->apply($text);
    }

    public static function details($content)
    {
        $regexpSp = '/\{details(?!.*\{details)(\s?)(?(1)(.*?))\}(.*?)\{\/details\}/is';
        while (preg_match($regexpSp, $content)) {
            $content = preg_replace($regexpSp, "<details><summary>" . __('app.see_more') . "</summary>$2$3</details>", $content);
        }

        $regexpFt = '/\{foto(?!.*\{foto)(\s?)(?(1)(.*?))\}(.*?)\{\/foto\}/is';
        while (preg_match($regexpFt, $content)) {
            $content = preg_replace($regexpFt, "<foto>$2$3</foto>", $content);
        }

        $regexpAu = '/\{auth(?!.*\{auth)(\s?)(?(1)(.*?))\}(.*?)\{\/auth\}/is';
        while (preg_match($regexpAu, $content)) {
            if (UserData::checkActiveUser()) {
                $content = preg_replace($regexpAu, "<dev class=\"txt-closed\">$2$3</dev>", $content);
            } else {
                $content = preg_replace($regexpAu, "<dev class=\"txt-closed\">" . __('app.text_closed') . "...</dev>", $content);
            }
        }

        return $content;
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

       	$beforeCut =  str_replace('[^1]', '', $beforeCut);

        return ['content' => $beforeCut, 'button' => $button];
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

    public static function facets($content)
    {
        preg_match_all('/#([^#,:\s,]+)/i', strip_tags($content), $matchs);

        if (is_array($matchs[1])) {

            $match_name = [];
            foreach ($matchs[1] as $key => $slug) {
                if (in_array($slug, $match_name)) {
                    continue;
                }

                $match_name[] = $slug;
            }

            $match_name = array_unique($match_name);

            arsort($match_name);

            foreach ($match_name as $key => $slug) {

                if ($info = ParserModel::getFacet($slug)) {
                    $content = str_replace('#' . $slug, '<img class="img-sm emoji mr5" alt="' . $info['facet_title'] . '" src="' . Img::PATH['facets_logo_small'] . $info['facet_img'] . '"><a href="/topic/' . $info['facet_slug'] . '">' . $info['facet_title'] . '</a>', $content);
                }
            }

            return $content;
        }
    }
	
    // Content management
    public static function noHTML(string $content, int $lenght = 150)
    {
        $content = Markdown::defaultTransform($content);

        $content = str_replace(["\r\n", "\r", "\n", "#"], ' ', $content);

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
