<?php
/*
 * Главные, функциональные настройки сайта
 * Main, functional site settings 
 */

return [

    // TRUE - by invitation only
    // TRUE - только по приглашению
    'invite'            => false,

    // To force an update (css)
    'version'     	=> 27,

    // Default localization (+ languages represented)
    // Локализация по умолчанию (+ какие языки есть в системе)
    'lang'  => 'ru',
    'languages'     => [
        'ru'        => 'Русский',
        'en'        => 'English',
    ],

    // Email of the site administration
    // Email администрации сайта
    'email'             => 'libarea@yandex.ru',

    // Confirm sender (email must be configured on the server).
    // Подтвердить отправителя (email должен быть настроен на сервере).
    'confirm_sender'    =>  false,


    // Captcha. If incl. - true, then we register the keys below
    // Капча. Если вкл. - true, то прописываем ключи ниже
    'captcha'               => false, 
    'captcha_public_key'    => '******',
    'captcha_private_key'   => '******',


	/*
	 * Css и JS
	 */

    // Path to svg sprite
    // Путь к спрайту svg
    'svg_path'  	=> '/assets/svg/icons.svg',
    
    // Paths to template files
	// Separate style files that may not be included in the templates
	// Отдельные файлы стилей, которые могут не включаться в шаблоны
    'path_css_admin' => [
        'style'	=> '/resources/views/assets/css/build.css',
    ],

    // Для статического сайта
    'path_css_build' => [
        'style'	=> '/resources/views/assets/css/build_html.css',
    ],

    // Base path to js files
    // Базовый путь к js файлам    
    'path_js_admin' => [
        'la'            => '/resources/views/assets/js/',
        'common'        => '/resources/views/assets/js/', 
        'admin'         => '/resources/views/assets/js/', 
        'zooom'   		=> '/resources/views/assets/js/',
        'app'           => '/resources/views/assets/js/', 
     ],  
	
	/*
	 * Для построение статики
	 */
	'url_html'	=>  'http://wiki-view.local/',
	 
	 
	// Пусть до сайта, где будут находиться HTML файлы
	// HLEB_GLOBAL_DIR + path_html
	'path_html'	=>  '/../wiki-view.local/',
	
	/*
	 * Menu in the admin panel
	 * Меню в админ-панели
	 */
	'menu-admin' => [
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
		], 	[
			'url'   => 'item.form.add',
			'title' => 'app.add_fact',
			'icon'  => 'write',
			'id'    => 'add_fact',
			'tl'    => 10,
		], [
			'url'   => 'items',
			'title' => 'app.facts',
			'icon'  => 'post',
			'id'    => 'facts',
			'tl'    => 10,
		], 
	],

  // To check existing facet types
  // Для проверки существующих типов фасетов
  'permitted'     => ['category'],

];
