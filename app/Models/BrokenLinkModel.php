<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Отчёт о битых внутренних ссылках между фактами.
 */
class BrokenLinkModel extends Model
{
    // Полная перезапись отчёта (вызывается при сборке)
    public static function replace(array $rows): void
    {
        DB::run("TRUNCATE TABLE broken_links");

        if (!$rows) {
            return;
        }

        $sql = "INSERT INTO broken_links (item_id, article_title, link, word) VALUES ";
        $chunks = [];
        $params = [];

        foreach ($rows as $i => $r) {
            $chunks[] = "(:i{$i}, :t{$i}, :l{$i}, :w{$i})";
            $params["i{$i}"] = (int)$r['item_id'];
            $params["t{$i}"] = $r['article_title'];
            $params["l{$i}"] = $r['link'];
            $params["w{$i}"] = $r['word'];
        }

        DB::run($sql . implode(', ', $chunks), $params);
    }

    // Очистить отчёт
    public static function clear(): void
    {
        DB::run("TRUNCATE TABLE broken_links");
    }

    // Количество жёстких битых ссылок (выделяем только URL-ссылки, не вики-термины)
    public static function countUrls(): int
    {
        return (int)DB::run(
            "SELECT COUNT(*) c FROM broken_links WHERE link <> ''"
        )->fetch()['c'];
    }

    // Список жёстких битых ссылок [текст](/путь/slug.html) с пагинацией.
    // Вики-термины (word != '', link = '') показывает отдельная секция терминов — не дублируем.
    public static function allUrls(int $page = 1, int $limit = 50): false|array
    {
        $start = ($page - 1) * $limit;

        $sql = "SELECT id, item_id, article_title, link, word, created_at
                FROM broken_links
                WHERE link <> ''
                ORDER BY id DESC
                LIMIT :start, :limit";

        return DB::run($sql, ['start' => $start, 'limit' => $limit])->fetchAll();
    }

    // Количество битых
    public static function count(): int
    {
        return (int)DB::run("SELECT COUNT(*) c FROM broken_links")->fetch()['c'];
    }
}