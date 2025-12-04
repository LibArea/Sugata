<?php

declare(strict_types=1);

class Meta
{
    public static function get(string $title = '', string $description = '', array $m = []): string
    {
		$url = config('meta', 'url');
		$name = config('meta', 'name');
		
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

                $output .= "<meta name=\"twitter:card\" content=\"summary\">"
                    . "<meta name=\"twitter:title\" content=\"$title\">"
                    . "<meta name=\"twitter:url\" content=\"" . $url . $m['url'] . "\">"
                    . "<meta property=\"twitter:description\" content=\"$description\">";

                if (!empty($m['imgurl'])) {
                    $output .= "<meta property=\"twitter:image\" content=\"" . $url . $m['imgurl'] . "\">";
                }
            }

            if (!empty($m['indexing'])) {
                $output .= "<meta name=\"robots\" content=\"noindex\">";
            }
        }

        return $output;
    }

    public static function home()
    {
        $meta = [
            'main'      => 'main',
            'og'        => true,
            'imgurl'    => config('meta', 'img_path'),
            'url'       => '/',
        ];

        return self::get(config('meta', 'home_title'), config('meta',  'home_desc'), $meta);
    }

    public static function publication(string $type, array $content): string
    {
            $meta = [
                'published_time' => $content['item_date'],
                'type'      => 'article',
                'og'        => true,
                'imgurl'    => false,
              //  'url'       => post_slug($type, (int)$content['item_id'], $content['item_slug']),
            ];
  
		
		$content['item_title'] = fragment($content['item_content'], 80);


        $description  = (fragment($content['item_content'], 250) == '') ? strip_tags($content['item_title']) : fragment($content['item_content'], 250);

        return self::get(htmlEncode(strip_tags($content['item_title'])), htmlEncode($description), $meta);
    }


    public static function category(array $facet): string
    {
		$url    = url('homepage');
		$title  = $facet['facet_seo_title'] . ' — ' . __('app.facts');
		$description = __('app.feed_facts') . $facet['facet_description'];

        $meta = [
            'og'        => true,
            'imgurl'    => Img::PATH['facets_logo'] . $facet['facet_img'],
            'url'       =>  $url,
        ];

        return self::get($title, $description, $meta);
    }
}
