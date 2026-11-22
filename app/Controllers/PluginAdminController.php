<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Controller;
use Hleb\Static\Request;
use Hleb\Static\Response;
use App\Support\Hooks;
use Meta, Msg;

/**
 * Управление плагинами системы хуков.
 */
class PluginAdminController extends Controller
{
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
                    'hooks'   => Hooks::points(),
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

    // Диспетчер админ-экранов плагина.
    // URL: /mod/admin/plugins/admin/{plugin}/{path}
    // Маршрут защищён middleware админа; метод подбирается строго по manifest.
    public function screen(): void
    {
        $name = (string)Request::param('plugin')->asString('');
        $path = (string)Request::param('path')->asString('') ?? '';

        // Плагин должен существовать и иметь админ-экраны.
        if ($name === '' || !Hooks::exists($name) || !Hooks::hasAdmin($name)) {
            Response::setStatus(404);
            render('/404', ['meta' => Meta::get(__('app.404'))]);
            return;
        }

        $verb = strtoupper((string)Request::getMethod());
        $callback = Hooks::resolveRoute($name, $verb, $path);
        if ($callback === null) {
            Response::setStatus(404);
            render('/404', ['meta' => Meta::get(__('app.404'))]);
            return;
        }

        // Вызов {Class}::{method}. Экран может либо вернуть строку-HTML,
        // либо сам вызвать render()/redirect. Никакой произвольный код из URL.
        call_user_func($callback);
    }
}