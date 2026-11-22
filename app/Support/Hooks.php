<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Система плагинов на хуках.
 *
 * Каждый плагин — отдельная папка в app/Plugins/<Имя>/ с файлом manifest.php:
 *
 *   app/Plugins/Demo/
 *     Demo.php            класс плагина (namespace App\Plugins\Demo)
 *     manifest.php        метаданные и объявление хуков
 *
 * manifest.php возвращает массив:
 *
 *   return [
 *     'name'        => 'Demo',
 *     'version'     => '0.1',
 *     'description' => '...',
 *     'hooks'       => [
 *        // [точка, 'PRE'|'POST', 'Класс::метод', вкл_флаг]
 *        ['site_page_footer', 'POST', 'Demo::render_menu', 1],
 *     ],
 *   ];
 *
 * Чтобы подключить плагин, достаточно положить папку в app/Plugins/ —
 * ядро найдёт её автоматически. Состояние «вкл/выкл» хранится отдельно
 * в storage/cache/plugins-state.php и не трогает сам плагин.
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
     * Каталог, где лежат плагины (каждая подпапка — плагин).
     */
    protected static function pluginsDir(): string
    {
        return dirname(__DIR__, 1) . '/Plugins';
    }

    /**
     * Файл состояния (вкл/выкл) плагинов. Отдельно от самих плагинов,
     * чтобы включение/выключение не меняло файлы в app/Plugins/.
     */
    protected static function statePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/cache/plugins-state.php';
    }

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
     * Загружает плагины (autodiscovery) и собирает flat-таблицы pre/post хуков.
     *
     * Плагин активен, если его папка существует и в состоянии
     * (storage/cache/plugins-state.php) он не помечен как выключенный.
     */
    public static function load(?string $dir = null): void
    {
        if (static::$loaded) {
            return;
        }
        static::$loaded = true;

        static::$preHooks  = [];
        static::$postHooks = [];

        $dir = $dir ?? static::pluginsDir();
        if (!is_dir($dir)) {
            return;
        }

        $state = static::readState();

        foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $pluginDir) {
            $name = basename($pluginDir);
            $manifestPath = $pluginDir . '/manifest.php';
            if (!is_file($manifestPath)) {
                continue;
            }
            $manifest = require $manifestPath;
            if (!is_array($manifest) || empty($manifest['hooks']) || !is_array($manifest['hooks'])) {
                continue;
            }
            $namespace = 'App\\Plugins\\' . $name;

            foreach ($manifest['hooks'] as $hook) {
                if (!is_array($hook) || count($hook) < 3) {
                    continue;
                }
                [$hookName, $stage, $callback] = $hook;
                // Вкл/выкл: выключен только если в состоянии явно false.
                $enabled = ($state[$name] ?? null) !== false && (bool)($hook[3] ?? 1);
                if (!$enabled) {
                    continue;
                }
                // Callback: строка 'Class::method' нормализуется до пространства имён плагина,
                // замыкание (Closure) используется как есть.
                if (is_string($callback)) {
                    if (str_starts_with($callback, 'App\\')) {
                        $fqcn = $callback;
                    } else {
                        $fqcn = $namespace . '\\' . ltrim($callback, '\\');
                    }
                } elseif ($callback instanceof \Closure) {
                    $fqcn = $callback;
                } else {
                    continue;
                }
                $hookKey = mb_strtolower((string)$hookName);
                $stage   = mb_strtoupper((string)$stage);
                $map = $stage === 'POST' ? 'postHooks' : 'preHooks';
                static::$$map[$hookKey][] = $fqcn;
            }
        }
    }

    /**
     * Возвращает список доступных плагинов с метаданными и текущим состоянием.
     *
     * @return array<int, array{name:string, title:string, version:string, description:string, active:bool, hooks_count:int}>
     */
    public static function plugins(?string $dir = null): array
    {
        $dir = $dir ?? static::pluginsDir();
        if (!is_dir($dir)) {
            return [];
        }

        $state = static::readState();
        $list = [];

        foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $pluginDir) {
            $name = basename($pluginDir);
            $manifestPath = $pluginDir . '/manifest.php';
            if (!is_file($manifestPath)) {
                continue;
            }
            $manifest = require $manifestPath;
            if (!is_array($manifest)) {
                continue;
            }
            $hooks = is_array($manifest['hooks'] ?? null) ? $manifest['hooks'] : [];
            $active = ($state[$name] ?? null) !== false && count($hooks) > 0;

            $list[] = [
                'name'        => $name,
                'title'       => (string)($manifest['name'] ?? $name),
                'version'     => (string)($manifest['version'] ?? ''),
                'description' => (string)($manifest['description'] ?? ''),
                'active'      => $active,
                'hooks_count' => count($hooks),
            ];
        }

        usort($list, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $list;
    }

    /**
     * Проверяет существование плагина по имени (есть ли папка с manifest).
     */
    public static function exists(string $name, ?string $dir = null): bool
    {
        return is_file(trim($dir ?? static::pluginsDir(), '/\\') . '/' . $name . '/manifest.php');
    }

    /**
     * Включает/выключает плагин, записывая состояние в storage/cache.
     * Плагин должен существовать; сама папка плагина не модифицируется.
     */
    public static function setEnabled(string $name, bool $enabled, ?string $dir = null): bool
    {
        $dir = $dir ?? static::pluginsDir();
        if (!static::exists($name, $dir)) {
            return false;
        }

        $state = static::readState();
        if ($enabled) {
            unset($state[$name]); // «включён по умолчанию» — просто не помечаем выключенным
        } else {
            $state[$name] = false;
        }

        return static::writeState($state);
    }

    /**
     * Сбрасывает загруженное состояние (используется в админке после переключения).
     */
    public static function reset(): void
    {
        static::$loaded  = false;
        static::$config  = null;
        static::$preHooks  = [];
        static::$postHooks = [];
    }

    /**
     * Читает файл состояния плагинов (название => вкл/выкл).
     *
     * @return array<string,bool>
     */
    public static function readState(): array
    {
        $path = static::statePath();
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * Записывает файл состояния плагинов. Создаёт каталог при необходимости.
     */
    public static function writeState(array $state): bool
    {
        $path = static::statePath();
        $dir  = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }
        $export = "<?php\n\n// Состояние (вкл/выкл) плагинов: имя => false означает выключен.\nreturn " . var_export($state, true) . ";\n";
        return (bool)file_put_contents($path, $export);
    }
}