<?php

namespace App\Controllers\Facet;

use Hleb\Base\Controller;
use App\Models\FacetModel;
use Meta, Html, Msg;

class FacetController extends Controller
{
    protected const LIMIT = 15;
    protected $user_count = 0;

    public function structure()
    {
        $type = 'category';

        render(
            '/content/facets/structure',
            [
                'meta'  => Meta::get(__('app.structure')),
                'data'  => [
                    'type'     => $type,
                    'sheet'    => 'structure',
                    'nodes'    => Html::builder(0, 0, FacetModel::getTree()),
                ]
            ]
        );
    }
}
