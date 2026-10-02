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
    return config('general', 'url_html') . '/' . $facet_path . '/' . $slug . '.html';
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
    return Parser::parse($content, $type);
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

function host(string $url)
{
    $parse  =  parse_url($url);
    return $parse['host'] ?? false;
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


/*

-- Таблица источников
CREATE TABLE sources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(2048) UNIQUE NOT NULL,
    normalized_url VARCHAR(2048) NOT NULL, -- для сравнения
    title VARCHAR(512),
    domain VARCHAR(255),
    status ENUM('unchecked', 'ok', 'broken', 'redirect', 'timeout') DEFAULT 'unchecked',
    http_code INT DEFAULT NULL,
    last_checked_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_last_checked (last_checked_at)
);

-- Связь статья ↔ источник (многие-ко-многим)
CREATE TABLE article_sources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    source_id INT UNSIGNED NOT NULL,
    citation_text TEXT,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    UNIQUE KEY uk_article_source (article_id, source_id),
    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE
);

*/
function checkSourceLink(string $url, array $options = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_USERAGENT => 'WikiBot/1.0 (+https://yourdomain.com/bot)',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if ($error) return ['status' => 'timeout', 'code' => 0, 'final_url' => $url];
    if ($httpCode >= 200 && $httpCode < 400) return ['status' => 'ok', 'code' => $httpCode, 'final_url' => $finalUrl];
    if ($httpCode == 0) return ['status' => 'timeout', 'code' => 0, 'final_url' => $url];
    
    return ['status' => 'broken', 'code' => $httpCode, 'final_url' => $finalUrl];
}