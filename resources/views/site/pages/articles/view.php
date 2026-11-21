<?php
/* Просмотр статьи — тонкий wrapper над общим layout статьи.
 * Все данные передаются в site/layouts/article.php ($type = 'articles').
 */
$related   = $related ?? [];
$similar   = $similar ?? [];
$faq       = $faq ?? [];
$wikiTerms = $wikiTerms ?? [];
$backlinks = $backlinks ?? [];

insert('/site/layouts/article', [
    'item'       => $item,
    'type'       => 'articles',
    'preview'    => !empty($preview),
    'mod'        => !empty($preview) ? 'preview' : 'static',
    'meta'       => $meta,
    'breadcrumb' => $breadcrumb,
    'activeRoot' => 'articles',
    'toc'        => build_article_toc((string)($item['item_content'] ?? '')),
    'related'    => $related,
    'similar'    => $similar,
    'faq'        => $faq,
    'wikiTerms'  => $wikiTerms,
    'backlinks'  => $backlinks,
    // У статей картинка идёт отдельным figure только если нет инфобокса
    'infobox'    => false,
]);