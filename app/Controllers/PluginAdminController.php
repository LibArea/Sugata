<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use App\Support\Hooks;
use Meta, Msg;

/**
 * Управление плагинами системы хуков.
 */
class PluginAdminController extends Controller
{
    // Доступные точки хуков: имя => описание (для справки в админке).
    private const HOOKS = [
        'site_article_before'          => 'Перед телом статьи (PRE может перехватить вывод).',
        'site_article_body'            => 'Тело статьи: PRE — подменить контент, POST — дополнить HTML.',
        'site_article_related_before'  => 'Перед блоком «Продолжить открывать».',
        'site_article_backlinks'       => 'Блок обратных ссылок.',
        'site_article_ldjson'          => 'JSON-LD разметка (POST получает массив данных).',
        'site_page_footer'             => 'Футер страницы (POST — добавить подпись/счётчик).',
    ];

    // Список плагинов с возможностью включать/выключать.
    public function index(): void
    {
        $plugins = Hooks::plugins();

        render(
            '/admin/pages/plugins/index',
            [
                'meta' => Meta::get(__('app.plugins')),
                'data' => [
                    'sheet'   => 'plugins',
                    'plugins' => $plugins,
                    'hooks'   => self::HOOKS,
                ],
            ]
        );
    }

    // Вкл/выкл плагина. Состояние хранится отдельно от самого плагина
    // (storage/cache/plugins-state.php), поэтому файлы плагина не меняются.
    public function toggle(): void
    {
        $data = Request::getParsedBody();
        $name = trim((string)($data['plugin_name'] ?? ''));
        if ($name === '' || !Hooks::exists($name)) {
            Msg::redirect(__('msg.no_content'), 'error', url('plugins'));
        }

        $nowActive = false;
        foreach (Hooks::plugins() as $p) {
            if ($p['name'] === $name) {
                $nowActive = (bool)$p['active'];
                break;
            }
        }

        if (!Hooks::setEnabled($name, !$nowActive)) {
            Msg::redirect(__('msg.plugin_save_error'), 'error', url('plugins'));
        }

        Hooks::reset(); // сбросить кэш диспетчера
        Msg::redirect(__('msg.plugin_saved'), 'success', url('plugins'));
    }
}