<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Проверка источников фактов (схема sources из раннего наброска).
 */
class SourceModel extends Model
{
    // Непроверенные источники
    public static function unchecked(int $limit = 50): false|array
    {
        $sql = "SELECT id, url, title FROM sources
                WHERE status = 'unchecked' OR last_checked_at IS NULL
                ORDER BY id DESC LIMIT :limit";

        return DB::run($sql, ['limit' => $limit])->fetchAll();
    }

    // Источники с ошибками (битые, таймаут, редирект)
    public static function errors(int $page = 1, int $limit = 50): false|array
    {
        $start = ($page - 1) * $limit;

        $sql = "SELECT s.id, s.url, s.title, s.domain, s.status, s.http_code,
                       s.last_checked_at, s.created_at,
                       COUNT(a_s.article_id) AS facts_count
                FROM sources s
                LEFT JOIN article_sources a_s ON a_s.source_id = s.id
                WHERE s.status IN ('broken', 'timeout', 'redirect')
                GROUP BY s.id
                ORDER BY s.last_checked_at DESC
                LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    // Количество источников с ошибками
    public static function errorsCount(): int
    {
        return (int)DB::run("SELECT COUNT(*) c FROM sources WHERE status IN ('broken', 'timeout', 'redirect')")->fetch()['c'];
    }

    // Факты без источника (нет связи article_sources)
    public static function factsWithoutSource(int $page = 1, int $limit = 50): false|array
    {
        $start = ($page - 1) * $limit;

        $sql = "SELECT i.item_id, i.item_title, i.item_slug, i.item_date
                FROM items i
                LEFT JOIN article_sources a_s ON a_s.article_id = i.item_id
                LEFT JOIN sources s ON s.id = a_s.source_id
                WHERE i.item_published = 1 AND i.item_is_deleted = 0
                  AND a_s.source_id IS NULL
                ORDER BY i.item_id DESC
                LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    // Количество фактов без источника
    public static function factsWithoutSourceCount(): int
    {
        $sql = "SELECT COUNT(*) c
                FROM items i
                LEFT JOIN article_sources a_s ON a_s.article_id = i.item_id
                WHERE i.item_published = 1 AND i.item_is_deleted = 0
                  AND a_s.source_id IS NULL";

        return (int)DB::run($sql)->fetch()['c'];
    }

    // Все источники (для админки), с количеством связанных фактов
    public static function all(int $page = 1, int $limit = 50): false|array
    {
        $start = ($page - 1) * $limit;

        $sql = "SELECT s.id, s.url, s.title, s.domain, s.status, s.http_code,
                       s.last_checked_at, s.created_at,
                       COUNT(a_s.article_id) AS facts_count
                FROM sources s
                LEFT JOIN article_sources a_s ON a_s.source_id = s.id
                GROUP BY s.id
                ORDER BY s.created_at DESC
                LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    // Факты, ссылающиеся на источник (для перехода в редактирование)
    public static function facts(int $source_id): false|array
    {
        $sql = "SELECT i.item_id, i.item_title, i.item_slug
                FROM article_sources a_s
                INNER JOIN items i ON i.item_id = a_s.article_id
                WHERE a_s.source_id = :id AND i.item_is_deleted = 0
                ORDER BY i.item_id DESC";

        return DB::run($sql, ['id' => $source_id])->fetchAll();
    }

    public static function count(): int
    {
        return (int)DB::run("SELECT COUNT(*) c FROM sources")->fetch()['c'];
    }

    // Обновление результата проверки (и названия источника, если получено)
    public static function updateResult(int $id, string $status, int $httpCode, string $title = ''): void
    {
        if ($title !== '') {
            $sql = "UPDATE sources SET status = :status, http_code = :code, title = :title, last_checked_at = NOW()
                    WHERE id = :id";
            DB::run($sql, ['status' => $status, 'code' => $httpCode, 'title' => $title, 'id' => $id]);
            return;
        }

        $sql = "UPDATE sources SET status = :status, http_code = :code, last_checked_at = NOW()
                WHERE id = :id";
        DB::run($sql, ['status' => $status, 'code' => $httpCode, 'id' => $id]);
    }

    // Общее количество по статусам
    public static function stats(): array
    {
        $rows = DB::run("SELECT status, COUNT(*) c FROM sources GROUP BY status")->fetchAll();

        $result = ['unchecked' => 0, 'ok' => 0, 'broken' => 0, 'redirect' => 0, 'timeout' => 0];
        foreach ($rows as $r) {
            $result[$r['status']] = (int)$r['c'];
        }

        return $result;
    }

    // Проверка ссылки: GET-запрос (чтобы можно было достать <title>) + статус.
    // Возвращает ['status', 'code', 'title' (если страница ответила и есть <title>)].
    public static function checkUrl(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'SugataSourceCheck/1.0 (Mozilla/5.0 compatible)',
        ]);

        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'timeout', 'code' => 0, 'title' => ''];
        }

        $status = 'broken';
        if ($httpCode >= 200 && $httpCode < 400) {
            $status = 'ok';
        }
        if ($httpCode === 0) {
            $status = 'timeout';
        }

        $title = '';
        if ($status === 'ok' && is_string($body) && $body !== '') {
            $title = self::extractTitle($body);
        }

        return ['status' => $status, 'code' => $httpCode, 'title' => $title];
    }

    // Достаёт <title> из HTML страницы
    protected static function extractTitle(string $html): string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $m)) {
            $title = html_entity_decode(trim((string)($m[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $title = (string)preg_replace('/\s+/u', ' ', (string)$title);
            return mb_substr($title, 0, 500);
        }
        return '';
    }
}