<?php

declare(strict_types=1);

namespace App\Controllers\Item;

use Hleb\Base\Controller;

use Hleb\Static\Request;
use App\Models\ItemModel;

class ItemController extends Controller
{
public function index()
    {
        $id = Request::param('id')->asInt();
        $item = ItemModel::getItem($id, 'id');
        if (empty($item)) {
            notEmptyOrView404([]);
            return;
        }

        // Единообразие в админке: все просмотры идут через предпросмотр (/mod/admin/preview/...),
        // чтобы админ видел то же, что и пользователь, а внутренние ссылки работали.
        $dir = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
        $facetPath = trim($dir[2] ?? '', '/');
        if ($facetPath === '' || empty($item['item_slug'])) {
            notEmptyOrView404([]);
            return;
        }

        $this->response()->redirect(urlItem($facetPath, $item['item_slug'], 'preview'), code: 303);
    }
}
