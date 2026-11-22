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
        Hooks::load(); // гарантируем, что конфиг прочитан

        $configPath = plugin_config_path();
        $plugins = [];
        if (is_file($configPath)) {
            $config = require $configPath;
            if (is_array($config)) {
                foreach ($config as $name => $item) {
                    $meta = is_array($item) && isset($item['meta']) && is_array($item['meta'])
                        ? $item['meta']
                        : [];
                    $hooks = is_array($item) && isset($item['hooks']) && is_array($item['hooks'])
                        ? $item['hooks']
                        : [];
                    // Плагин считается активным, если активен хоть один его хук.
                    $active = false;
                    foreach ($hooks as $h) {
                        if (is_array($h) && !empty($h[3])) {
                            $active = true;
                            break;
                        }
                    }
                    $plugins[] = [
                        'name'        => (string)$name,
                        'title'       => (string)($meta['name'] ?? $name),
                        'version'     => (string)($meta['version'] ?? ''),
                        'author'      => (string)($meta['author'] ?? ''),
                        'description' => (string)($meta['description'] ?? ''),
                        'active'      => $active,
                        'hooks_count' => count($hooks),
                    ];
                }
            }
        }
        ksort($plugins);

        render(
            '/admin/pages/plugins/index',
            [
                'meta' => Meta::get(__('app.plugins')),
                'data' => [
                    'sheet'   => 'plugins',
                    'plugins' => $plugins,
                    'hooks'   => self::HOOKS,
                    'config_path' => $configPath,
                ],
            ]
        );
    }

    // Вкл/выкл плагина (переключает флаг [3] у всех его хуков).
    public function toggle(): void
    {
        $data = Request::getParsedBody();
        $name = trim((string)($data['plugin_name'] ?? ''));
        if ($name === '') {
            Msg::redirect(__('msg.no_content'), 'error', url('plugins'));
        }

        $configPath = plugin_config_path();
        if (!is_file($configPath)) {
            Msg::redirect(__('msg.no_content'), 'error', url('plugins'));
        }

        // Читаем конфиг, меняем флаги активных хуков на противоположные только для этого плагина.
        $config = require $configPath;
        if (!is_array($config) || !isset($config[$name]) || !is_array($config[$name])) {
            Msg::redirect(__('msg.no_content'), 'error', url('plugins'));
        }

        $plugin = $config[$name];
        $anyOn = false;
        foreach (($plugin['hooks'] ?? []) as $h) {
            if (is_array($h) && !empty($h[3])) {
                $anyOn = true;
                break;
            }
        }
        $newState = $anyOn ? 0 : 1;

        foreach (($plugin['hooks'] ?? []) as $i => $h) {
            if (!is_array($h)) {
                continue;
            }
            // Минимальный формат ['point', 'PRE'|'POST', 'Class::method'] + опц. флаг.
            $plugin['hooks'][$i][3] = (int)$newState;
        }
        $config[$name] = $plugin;

        // Сохраняем конфиг как читаемый PHP-файл.
        $export = "<?php\n\nreturn " . self::varExport($config) . ";\n";
        if (file_put_contents($configPath, $export) === false) {
            Msg::redirect(__('msg.plugin_save_error'), 'error', url('plugins'));
        }

        Hooks::reset(); // сбросить кэш диспетчера
        Msg::redirect(__('msg.plugin_saved'), 'success', url('plugins'));
    }

    /**
     * Готовит PHP-вариант конфига в формате, который переживёт повторный require.
     * Используем var_export с нормализацией.
     */
    protected static function varExport(array $config): string
    {
        $out = var_export($config, true);
        $out = preg_replace('/=> \n\s*array \(/', '=> [', $out) ?? $out;
        $out = str_replace('array (', '[', $out);
        $out = str_replace(')', ']', $out);
        $out = str_replace("=> \n", '=> ', $out);
        return $out;
    }
}