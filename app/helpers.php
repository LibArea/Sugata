<?php

declare(strict_types=1);

use Hleb\Static\Request;
use Hleb\Static\Container;

/*
 * Global "helper" functions.
 *
 * Глобальные «вспомогательные» функции.
 */

// @param  string|null $key
function __(?string $key, array $params = [])
{
    if ($key === null) {
        return $key;
    }

    return Translate::get($key, $params);
}

function fact_slug(string $facet_path, string $slug)
{
    return config('general', 'url_html') . '/' . $facet_path . '/' . $slug . '.html';
}

/**
 * Нормализует выбранные категории из формы в список id.
 * Поддерживает как массив (facet_select[]), так и JSON-строку (старый формат Tagify).
 */
function facetIdsFromInput(mixed $value): array
{
    if (is_array($value)) {
        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    $ids = [];
    $topics = json_decode((string)$value, true);
    if (is_array($topics)) {
        foreach ($topics as $t) {
            if (is_array($t) && !empty($t['id'])) {
                $ids[] = (int)$t['id'];
            }
        }
    }

    return array_values(array_unique(array_filter($ids)));
}

function urlItem(string $facet_path, string $slug, string $mod = 'static'): string
{
    $path = trim($facet_path, '/');

    if ($mod === 'preview') {
        return config('general', 'url') . '/mod/admin/preview/' . $path . '/' . $slug . '.html';
    }

    return '/' . $path . '/' . $slug . '.html';
}

function is_current($url)
{
    $uri = Request::getUri()->getPath();

    if ($url == $uri) return true;

    $a = explode('?', $uri);
    if ($url == $a[0]) return true;

    return false;
}

function insert(string $hlTemplatePath, array $params = [])
{
    $params['container'] = Container::getContainer();

    extract($params);

    unset($params);

    $tpl_puth = DIRECTORY_SEPARATOR . trim($hlTemplatePath, '/\\');

    require TEMPLATES . DIRECTORY_SEPARATOR . $tpl_puth . '.php';
}

function render(string $name, array $data = [])
{
    $page_content = view($name, ['data' => $data['data']]);

    echo view('/admin/layout/main', ['content' => $page_content, 'data' => $data['data'], 'meta' => $data['meta']]);
}

function markdown(string $content, string $type = 'text', bool $preview = false)
{
    return Parser::parse($content, $type, $preview);
}

function fragment(string $content, int $limit = 0)
{
    return Parser::noHTML($content, $limit);
}

function notEmptyOrView404($params)
{
    if (empty($params)) {
        echo view('error', ['httpCode' => 404, 'message' => __('404.page_not') . ' <br> ' . __('404.page_removed')]);
        exit();
    }
    return true;
}

function htmlEncode($text)
{
    if ($text === null || is_scalar($text)) {
        $text = (string)($text ?? '');
    }

    return htmlspecialchars($text, ENT_QUOTES);
}

function langDate($time)
{
    return Html::langDate($time);
}

function breadcrumb($arrey)
{
    return Html::breadcrumb($arrey);
}

function pagination($pNum, $pagesCount, $sheet, $other, $sign = '?', $sort = null)
{
    return Html::pagination($pNum, $pagesCount, $sheet, $other, $sign, $sort);
}

function redirect(string $url): void
{
    $container = Container::getContainer();
    $container->redirect()->to($url, status: 303);
}

function modeDayNight()
{
    $container = Container::getContainer();

    $cookies = $container->cookies()->get('dayNight')->value();

    if ($cookies == 'dark') {
        return ' dark';
    }

    if ($cookies == 'light') {
        return ' light';
    }

    return (config('general', 'night_mode') == 'dark') ? ' dark' : ' light';
}

function urlDir($facet_path, $mod = 'dynamics')
{
    $path = trim((string)$facet_path, '/');

    if ($mod === 'static') {
        return '/' . $path;
    }

    if ($mod === 'preview') {
        return config('general', 'url') . '/mod/admin/preview/' . $path . '/';
    }

    return config('general', 'url') . '/mod/admin/dir/' . $path;
}