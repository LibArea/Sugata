<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Система плагинов на хуках.
 *
 * Диспетчер хуков: PRE-хуки плюс код по умолчанию плюс POST-хуки.
 * Плагин может перехватить выполнение (STOP) или модифицировать результат.
 *
 * Конфиг плагинов — PHP-файл (config/plugins.php), возвращающий
 * массив вида:
 *
 *   [
 *     'Menu' => [
 *       'meta'    => [...],
 *       'hooks'   => [
 *          ['site_page_footer', 'PRE',  'App\\Plugins\\Menu::render_menu', 1],
 *          ['site_page_body',   'POST', 'App\\Plugins\\Menu::patch_body',  1],
 *       ],
 *     ],
 *   ]
 *
 * Где 'hooks' элемент: [имя_хука, 'PRE'|'POST', 'Класс::метод', вкл_флаг].
 */
final class Hooks
{
    /// Специальные значения, управляющие цепочкой.
    public const STOP     = 1;
    public const CONTINUE = 2;

    protected static ?array $config = null;
    protected static array $preHooks  = [];
    protected static array $postHooks = [];
    protected static bool $loaded = false;

    /**
     * Главная точка входа. Выполняет PRE-хуки, затем код по умолчанию,
     * затем POST-хуки, и возвращает итоговый результат.
     *
     * @param string   $hook      имя точки (например 'site_page_body')
     * @param callable $default   код по умолчанию, выполняется если PRE не остановил
     * @param array    $args      аргументы, передаваемые в PRE-хуки и default
     */
    public static function dispatch(string $hook, callable $default, array $args = []): mixed
    {
        static::load();

        $hookKey = mb_strtolower($hook);
        // Вызовы всегда позиционные: ключи в $args не должны превращаться в именованные параметры.
        $pos = array_values($args);
        $result  = null;

        // PRE-хуки: могут перехватить выполнение default.
        // Возврат не-null значения означает перехват: это значение становится результатом.
        // Возврат null — продолжить цепочку; после всех PRE выполняется default.
        if (!empty(static::$preHooks[$hookKey])) {
            $result = null;
            foreach (static::$preHooks[$hookKey] as $callback) {
                $r = $callback(...$pos);
                if (!static::isStop($r)) {
                    $result = $r;
                    break; // PRE перехватил выполнение default
                }
            }
            if ($result === null) {
                $result = $default(...$pos);
            }
        } else {
            $result = $default(...$pos);
        }

        // POST-хуки: получают результат default и могут его изменить.
        if (!empty(static::$postHooks[$hookKey])) {
            foreach (static::$postHooks[$hookKey] as $callback) {
                $r = $callback($result, ...$pos);
                if (!static::isStop($r)) {
                    $result = $r;
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает true, если callback вернул управляющее значение STOP.
     */
    public static function isStop(mixed $value): bool
    {
        return $value === self::STOP;
    }

    /**
     * Загружает конфиг плагинов и собирает flat-таблицы pre/post хуков.
     */
    public static function load(?string $configPath = null): void
    {
        if (static::$loaded) {
            return;
        }
        static::$loaded = true;

        $path = $configPath ?? self::defaultConfigPath();
        if (!is_file($path)) {
            return;
        }
        $config = require $path;
        if (!is_array($config)) {
            return;
        }
        static::$config = $config;

        static::$preHooks  = [];
        static::$postHooks = [];

        foreach ($config as $plugin => $item) {
            if (!is_array($item) || empty($item['hooks']) || !is_array($item['hooks'])) {
                continue;
            }
            foreach ($item['hooks'] as $hook) {
                if (!is_array($hook) || count($hook) < 3) {
                    continue;
                }
                [$hookName, $stage, $callback] = $hook;
                $enabled = (bool)($hook[3] ?? 1);
                if (!$enabled) {
                    continue;
                }
                $hookKey = mb_strtolower((string)$hookName);
                $stage   = mb_strtoupper((string)$stage);
                $map = $stage === 'POST' ? 'postHooks' : 'preHooks';
                static::$$map[$hookKey][] = $callback;
            }
        }
    }

    /**
     * Сбрасывает загруженный конфиг (используется в админке после изменения конфига).
     */
    public static function reset(): void
    {
        static::$loaded  = false;
        static::$config  = null;
        static::$preHooks  = [];
        static::$postHooks = [];
    }

    protected static function defaultConfigPath(): string
    {
        return dirname(__DIR__, 2) . '/config/plugins.php';
    }
}