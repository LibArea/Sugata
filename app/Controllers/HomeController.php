<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Meta;

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
                    'sheet' => 'home',
                ]
            ]
        );
    }


}
