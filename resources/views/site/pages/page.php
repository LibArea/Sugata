<?php
/* Просмотр служебной страницы (info и т.п.) — тонкий wrapper над общим layout статьи.
 * Все данные передаются в site/layouts/article.php ($type = 'page').
 */
$dir       = $dir ?? [];
$related   = $related ?? [];
$similar   = $similar ?? [];
$faq       = $faq ?? [];
$backlinks = $backlinks ?? [];

insert('/site/layouts/article', [
    'item'       => $item,
    'type'       => 'page',
    'preview'    => !empty($preview),
    'mod'        => !empty($preview) ? 'preview' : 'static',
    'meta'       => $meta,
    'breadcrumb' => $breadcrumb,
    'activeRoot' => $activeRoot ?? 'info',
    'toc'        => build_article_toc((string)($item['item_content'] ?? '')),
    'related'    => $related,
    'similar'    => $similar,
    'faq'        => $faq,
    'backlinks'  => $backlinks,
    'infobox'    => false,
]);