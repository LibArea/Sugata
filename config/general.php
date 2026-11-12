<?php

/*
 * Общие настройки сайта и статической сборки.
 */
return [
    'version'               => 103,

    // «Режим запуска»: первые 50 участников получают повышенный trust level
    'mode'                  => true,

    // Домен самого сайта (админка, предпросмотр)
    'url'                   => 'https://wiki.local',

    // Домен статической сборки
    'url_html'              => 'https://sugata.ru',

    // Каталог статики относительно корня проекта
    'path_html'             => '/../wiki-view.local',

    'site_name'             => 'Sugata',
    'template'              => 'default',
    'lang'                  => 'ru',

    // Регистрация только по инвайтам
    'invite'                => false,

    // Подтверждение e-mail
    'mail_check'            => false,

    // reCAPTCHA
    'captcha'               => false,
    'captcha_public_key'    => '',

    // Яндекс.Метрика
    'metrika_id'            => 106896487,

    // Тема по умолчанию: 'light' или 'dark'
    'night_mode'            => 'light',

    // Полнотекстовый поиск (S2). false — простой поиск по LIKE
    'search_engine'         => true,

    // Минимальная длина контента для «Факта дня»
    'fact_day_min_length'   => 1000,

    // Сервис скриншотов для каталога сайтов: {url} — адрес в urlencode
    'screenshot_service'    => 'https://s.wordpress.com/mshots/v1/{url}?w=1200',
    'screenshot_timeout'    => 10,

    // Сколько последних версий статьи хранить полностью (остальные — диффом)
    'history_full_versions' => 10,

    // Источники для минификации JS: [имя_сборки => каталог]
    'path_js_admin'         => [
        'admin'  => '/resources/views/assets/js/',
        'app'    => '/resources/views/assets/js/',
        'common' => '/resources/views/assets/js/',
        'la'     => '/resources/views/assets/js/',
    ],

    // Источники для минификации CSS: [имя_сборки => входной файл]
    'path_css_admin'        => [
        'app' => '/resources/views/assets/css/build_app.css',
    ],

    // Меню админ-панели
    'menu-admin'            => [
        [
            'url'   => 'structure',
            'title' => 'app.structure',
            'icon'  => 'git-merge',
            'id'    => 'structure',
            'tl'    => 10,
        ],
        [
            'url'   => 'tools',
            'title' => 'app.tools',
            'icon'  => 'tool',
            'id'    => 'tools',
            'tl'    => 10,
        ],
        [
            'url'   => 'item.form.add',
            'title' => 'app.add_fact',
            'icon'  => 'write',
            'id'    => 'add_fact',
            'tl'    => 1,
        ],
        [
            'url'   => 'setting',
            'title' => 'app.setting',
            'icon'  => 'settings',
            'id'    => 'setting',
            'tl'    => 1,
            'br'    => 'br',
        ],
    ],
];
