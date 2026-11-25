<?php
/* Просмотр факта — тонкий wrapper над общим layout статьи.
 * Все данные передаются в site/layouts/article.php ($type = 'facts').
 */
$dir     = $dir ?? [];
$related = $related ?? [];
$similar = $similar ?? [];
$faq     = $faq ?? [];
$backlinks = $backlinks ?? [];

insert('/site/layouts/article', [
    'item'       => $item,
    'type'       => 'facts',
    'preview'    => !empty($preview),
    'mod'        => !empty($preview) ? 'preview' : 'static',
    'meta'       => $meta,
    'breadcrumb' => $breadcrumb,
    'activeRoot' => 'facts',
    'toc'        => build_article_toc((string)($item['item_content'] ?? '')),
    'related'    => $related,
    'similar'    => $similar,
    'faq'        => $faq,
    'backlinks'  => $backlinks,
    // У фактов картинка может быть в инфобоксе/обложке отдельно
    'sideImg'    => article_side_image($item) !== '',
    'infobox'    => true,
]);