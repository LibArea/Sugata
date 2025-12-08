<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use App\Models\ItemModel;
use Meta, Html;

class HomeController extends Controller
{
    public function index(): void
    {
        $m = [
            'og'    => false,
            'url'   => '',
        ];

        render(
            '/index',
            [
                'meta'  => Meta::get(__('app.admin'), __('app.admin'), $m),
                'data'  => [
                    'sheet'         => 'home',
                ]
            ]
        );
    }

    public function tools()
    {
        render(
            '/content/tools',
            [
                'meta'  => Meta::get(__('app.tools')),
                'data'  => [
                    'sheet'         => 'tools',
                ]
            ]
        );
    }
}
