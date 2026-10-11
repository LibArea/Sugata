<?php

declare(strict_types=1);

class Meta
{
    public static function get(string $title = '', string $description = '', array $m = []): string
    {
		$url = config('meta', 'url');
		$name = config('meta', 'name');
		
		$title = htmlEncode($title);
		$description = htmlEncode($description);
		
        $title = $title ?: config('meta', 'title');
        $description = $description ?: $name;

        $output = '';

        $output .= empty($m['main']) ? "<title>$title | " . $name . "</title>" : "<title>$title</title>";
        $output .= "<meta name=\"description\" content=\"$description\">";

        if (!empty($m['published_time'])) {
            $output .= "<meta property=\"article:published_time\" content=\"{$m['published_time']}\">";
        }

        $output .= empty($m['type']) ? '<meta property="og:type" content="website">' : "<meta property=\"og:type\" content=\"{$m['type']}\">";

        if (!empty($m)) {
            if (!empty($m['url'])) {
                $output .= "<link rel=\"canonical\" href=\"" . $url . $m['url'] . "\">";
            }

            if (!empty($m['og'])) {
                $output .= "<meta property=\"og:title\" content=\"$title\">"
                    . "<meta property=\"og:description\" content=\"$description\">"
                    . "<meta property=\"og:url\" content=\"" . $url . $m['url'] . "\">";

                if (!empty($m['imgurl'])) {
                    $output .= "<meta property=\"og:image\" content=\"" . $url . $m['imgurl'] . "\">"
                        . "<meta property=\"og:image:width\" content=\"820\">"
                        . "<meta property=\"og:image:height\" content=\"320\">";
                }

            }

            if (!empty($m['indexing'])) {
                $output .= "<meta name=\"robots\" content=\"noindex\">";
            }
        }

        return $output;
    }

    // Обрезка строки до лимита (по словам, для title/description)
    protected static function cutLen(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        $cut = mb_substr($text, 0, $limit);
        $pos = mb_strrpos($cut, ' ');
        if ($pos !== false) {
            $cut = mb_substr($cut, 0, $pos);
        }
        return trim($cut) . '…';
    }

    public static function home()
    {
        $meta = [
            'main'      => 'main',
            'og'        => true,
            'imgurl'    => config('meta', 'img_path'),
            'url'       => '/',
        ];

        $title = self::cutLen((string)config('meta', 'home_title'), 65);
        $desc  = self::cutLen((string)config('meta', 'home_desc'), 160);

        return self::get($title, $desc, $meta);
    }

    public static function view(array $content, $facet_path, $img_url): string
    {
            $meta = [
                'published_time' => $content['item_date'],
                'type'      => 'article',
                'og'        => true,
                'imgurl'    => config('general', 'url_html') . $img_url,
				'url'    	=> fact_slug($facet_path, $content['item_slug']),
            ];

        // Если нет картинки статьи — дефолтная картинка бренда (для соцсетей)
        if (empty($img_url)) {
            $meta['imgurl'] = config('general', 'url_html') . config('meta', 'img_path');
        }

        $description  = (fragment($content['item_content'], 250) == '') ? strip_tags($content['item_title']) : fragment($content['item_content'], 250);
        $title = self::cutLen(html_entity_decode(strip_tags($content['item_title']), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 65);
        $description = self::cutLen(html_entity_decode((string)$description, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 160);

        // Служебные страницы (info) — noindex
        if ($facet_path === 'info') {
            $meta['indexing'] = true;
        }

        return self::get(htmlEncode($title), htmlEncode($description), $meta);
    }

    // Доп. SEO: JSON-LD CollectionPage для категории
    protected static function categoryJsonLd(array $facet): string
    {
        $url = config('general', 'url_html') . '/' . trim($facet['facet_path'], '/') . '/';
        $name = trim((string)($facet['facet_title'] ?? ''));

        $json = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => config('general', 'site_name'),
                'url' => config('general', 'url_html') . '/',
            ],
        ];

        return '<script type="application/ld+json">' . json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }

    public static function category(array $facet): string
    {
		$url    = config('general', 'url_html') . '/' . $facet['facet_path'] . '/';

		// SEO-title из базы (если заполнен), иначе "Название — Факты"
		$seoTitle = trim((string)($facet['facet_seo_title'] ?? ''));
		$title  = $seoTitle !== '' ? $seoTitle : $facet['facet_title'] . ' — ' . __('app.facts');
		$title  = self::cutLen(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 65);

		// Description: из поля facet_description, иначе — авто-шаблон
		$descText = trim((string)($facet['facet_description'] ?? ''));
		if ($descText === '') {
			$description = __('app.facts') . ' по теме «' . $facet['facet_title'] . '»: проверенные факты, источники и полезные материалы.';
		} else {
			$description = $descText;
		}
		$description = self::cutLen(html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 160);

        $meta = [
            'og'        => true,
            'imgurl'    => Img::PATH['facets_logo'] . $facet['facet_img'],
            'url'       =>  $url,
        ];

        // Служебная категория Информация — noindex
        if (trim($facet['facet_path'], '/') === 'info') {
            $meta['indexing'] = true;
        }

        return self::get($title, $description, $meta) . self::categoryJsonLd($facet);
    }
}
