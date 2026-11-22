<?php

declare(strict_types=1);

namespace App\Plugins\Demo;

use Meta;

/**
 * Демо-плагин, показывающий работу системы хуков.
 *
 * Использует POST-хук 'site_page_footer': добавляет подпись в футер страницы.
 */
final class Demo
{
    /**
     * Обработчик POST-хука site_page_footer.
     * Получает $result (результат default) и позиционные аргументы точки
     * (для site_page_footer это ['preview' => bool]).
     */
    public static function render_menu(string $footer, mixed $preview = null): string
    {
        $note = $preview
            ? __('app.demo_plugin_note') . ' — ' . __('app.demo_plugin_preview')
            : __('app.demo_plugin_note');

        return $footer . "\n" . '<div class="site-footer__in plugin-note">' . $note . '</div>';
    }

    /**
     * Экран плагина в админке (GET /mod/admin/plugins/admin/demo).
     * Отдаёт страницу через общий admin-layout.
     */
    public static function index(): void
    {
        \App\Support\Hooks::renderPluginView(
            'Demo',
            'screen',
            [
                'meta' => Meta::get(__('app.plugins')),
                'data' => [
                    'sheet' => 'plugin_demo',
                    'title' => __('app.demo_plugin_title'),
                    'text'  => __('app.demo_plugin_screen_text'),
                ],
            ]
        );
    }
}