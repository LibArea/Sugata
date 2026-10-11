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

    private function validateInput($input)
    {
        return preg_replace('/[^a-zA-ZА-Яа-я0-9 ]/ui', '', $input->asString());
    }

    public static function types()
    {
        return FacetModel::types();
    }
}
