<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Static\Request;
use Hleb\Base\Controller;
use App\Models\{FormModel, FacetModel};

class FormController extends Controller
{
    // Related posts, content author change, facets 
    // Связанные посты, изменение автора контента, фасеты
    public function index()
    {
        $type   = $this->validateInput(Request::param('type'));
        $input  = Request::input();
        $search = $input['q'] ?? '';
        $root   = trim((string)($input['root'] ?? ''));
        $excludeId = (int)($input['exclude_id'] ?? 0);

        return FormModel::get((string)$search, $type, $root !== '' ? $root : null, $excludeId);
    }

    // Проверка вики-ссылок [[Цель]]: возвращает, какие цели существуют в базе.
    // Принимает POST: terms[] — список строк-целей.
    // Возвращает JSON: { "term": 1|0, ... } (1 — статья найдена, 0 — нет).
    public function checkWikilinks()
    {
        $post  = Request::allPost();
        $terms = $post['terms'] ?? [];

        // Принимаем и строку и массив
        if (is_string($terms)) {
            $terms = array_values(array_filter(array_map('trim', explode(',', $terms))));
        }

        $result = [];
        foreach ($terms as $term) {
            $term = trim((string)$term);
            if ($term === '') {
                continue;
            }
            // Резолвер возвращает список; проверяем, что есть уникальный результат с рангом < 2
            $found = \App\Models\ItemModel::findByTerm($term);
            $result[$term] = 0;
            if (!empty($found)) {
                $bestRank = $found[0]['_rank'] ?? 2;
                if ($bestRank < 2) {
                    $ties = 0;
                    foreach ($found as $r) {
                        if (($r['_rank'] ?? 2) === $bestRank) {
                            $ties++;
                        }
                    }
                    if ($ties === 1) {
                        $result[$term] = 1;
                    }
                }
            }
        }

        return $result;
    }

    private function validateInput($input)
    {
        return preg_replace('/[^a-zA-ZА-Яа-я0-9 ]/ui', '', $input->asString());
    }

    public static function types()
    {
        return FacetModel::types();
    }
}
