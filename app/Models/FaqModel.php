<?php

declare(strict_types=1);

namespace App\Models;

use Hleb\Base\Model;
use Hleb\Static\DB;

/**
 * Вопросы-ответы (FAQ) для фактов.
 */
class FaqModel extends Model
{
    // Вопросы факта, в порядке сортировки
    public static function forItem(int $item_id): false|array
    {
        $sql = "SELECT id, item_id, question, answer, sort_order
                FROM item_faq
                WHERE item_id = :item_id
                ORDER BY sort_order ASC, id ASC";

        return DB::run($sql, ['item_id' => $item_id])->fetchAll();
    }

    // Полная замена списка FAQ для факта
    public static function replace(int $item_id, array $rows): void
    {
        DB::run("DELETE FROM item_faq WHERE item_id = :item_id", ['item_id' => $item_id]);

        foreach ($rows as $i => $row) {
            $question = trim((string)($row['question'] ?? ''));
            $answer   = trim((string)($row['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }

            DB::run(
                "INSERT INTO item_faq (item_id, question, answer, sort_order) VALUES (:item_id, :question, :answer, :sort)",
                [
                    'item_id'   => $item_id,
                    'question'  => $question,
                    'answer'    => $answer,
                    'sort'      => (int)($row['sort_order'] ?? $i),
                ]
            );
        }
    }
}