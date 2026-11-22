<?php

declare(strict_types=1);

/**
 * Метаданные и хуки плагина Demo.
 *
 * Достаточно положить эту папку в app/Plugins/ — плагин подхватится
 * автоматически. Включение/выключение хранится отдельно и не меняет этот файл.
 */
return [
    'name'        => 'Demo',
    'version'     => '0.1',
    'author'      => 'dev',
    'description' => 'Добавляет подпись в футер (POST-хук) и имеет собственный экран в админке.',
    'hooks'       => [
        // [точка, 'PRE'|'POST', 'Класс::метод' (в пространстве имён плагина), вкл_флаг]
        ['site_page_footer', 'POST', 'Demo::render_menu', 1],
    ],
    'admin'       => [
        'id'      => 'demo',
        'title'   => 'Демо плагина',
        'icon'    => 'layers',
        'section' => 'tools',
        'routes'  => [
            'GET /' => 'Demo::index',
        ],
    ],
];