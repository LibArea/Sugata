<?php

declare(strict_types=1);

namespace App\Plugins;

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
        if ($preview) {
            return $footer;
        }
        return $footer . "\n" . '<div class="site-footer__in plugin-note">' . __('app.demo_plugin_note') . '</div>';
    }
}