<?php

return [
  'Demo' => [
    'meta' => [
      'name' => 'Демо',
      'version' => '0.1',
      'author' => 'dev',
      'description' => 'Добавляет подпись в футер (демонстрация POST-хука].',
    ],
    'hooks' => [
      0 => [
        0 => 'site_page_footer',
        1 => 'POST',
        2 => 'App\\Plugins\\Demo::render_menu',
        3 => 1,
      ],
    ],
  ],
];
