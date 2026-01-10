<?php

declare(strict_types=1);

use Hleb\Static\Request;
use Hleb\Static\Container;
use App\Bootstrap\Services\User\UserData;

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
    return config('general', 'url_html') . $facet_path . '/' . $slug . '.html';
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

    echo view('/main', ['content' => $page_content, 'data' => $data['data'], 'meta' => $data['meta']]);
}

function closing()
{
    if (config('general', 'site_disabled')  && !UserData::checkAdmin()) {
        insert('site-off');
        exit();
    }

    return true;
}

function markdown(string $content, string $type = 'text')
{
    return App\Content\Parser::text($content, $type);
}

function fragment(string $content, int $limit = 0)
{
    return \App\Content\Parser::noHTML($content, $limit);
}

function notEmptyOrView404($params)
{
    if (empty($params)) {
        echo view('error', ['httpCode' => 404, 'message' => __('404.page_not') . ' <br> ' . __('404.page_removed')]);
        exit();
    }
    return true;
}

function host(string $url)
{
    $parse  =  parse_url($url);
    return $parse['host'] ?? false;
}

function htmlEncode($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES);
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

function urlDir($facet_path)
{
    return config('general', 'url_bild') .  DIRECTORY_SEPARATOR . $facet_path;
}
