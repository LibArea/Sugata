<?php

declare(strict_types=1);

namespace App\Content;

/**
 * Система тем с наследованием и аварийным фолбэком на дефолтную тему.
 *
 * Структура папок (относительно resources/views):
 *   themes/{name}/site/...    - шаблоны темы (переопределения своих страниц)
 *   themes/{name}/theme.php   - манифест: name, parent, css[], js[]
 *   themes/{name}/assets/     - дополнительные css/js темы (после базовых)
 *
 * Дефолтная тема — 'wiki'. Если тема из конфига не существует или сломана,
 * резолвер падает на 'wiki' (неприкосновенная база, всегда должна существовать).
 * Если файла нет ни в одной теме цепочки — возвращается исходный путь, и HLEB
 * честно укажет на отсутствующий файл.
 *
 * Конфиг: config('general','template') => 'wiki' (по умолчанию, при отсутствии).
 */
final class Theme
{
    private const THEME_ROOT = 'themes';
    private const DEFAULT_THEME = 'wiki';

    private static ?string $current = null;
    private static ?array $manifestCache = [];

    /**
     * Имя активной темы из конфига; если темы нет — 'wiki'.
     */
    public static function current(): string
    {
        if (self::$current === null) {
            $name = (string)config('general', 'template');
            $name = $name === '' ? self::DEFAULT_THEME : $name;
            // Указанная тема должна существовать, иначе безопасный фолбэк на wiki.
            if (!self::themeExists($name)) {
                $name = self::DEFAULT_THEME;
            }
            self::$current = $name;
        }
        return self::$current;
    }

    /**
     * Существует ли каталог темы.
     */
    public static function themeExists(string $name): bool
    {
        return is_dir(TEMPLATES . DIRECTORY_SEPARATOR . self::THEME_ROOT . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * Манифест темы (если есть), с дефолтами.
     *
     * @return array{name:string,parent:?string,css:string[],js:string[]}
     */
    public static function manifest(?string $name = null): array
    {
        $name = $name ?? self::current();
        if (isset(self::$manifestCache[$name])) {
            return self::$manifestCache[$name];
        }
        $file = TEMPLATES . DIRECTORY_SEPARATOR . self::THEME_ROOT . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'theme.php';
        $cfg  = is_file($file) ? (array)include $file : [];
        $cfg += ['name' => $name, 'parent' => null, 'css' => [], 'js' => []];
        self::$manifestCache[$name] = $cfg;
        return $cfg;
    }

    /**
     * Цепочка тем от активной вверх по родителям (активная первой), без дублей и зацикливаний.
     *
     * @return string[]
     */
    public static function chain(): array
    {
        $chain = [];
        $name  = self::current();
        $seen  = [];
        while ($name !== null && $name !== '' && !isset($seen[$name])) {
            $seen[$name] = true;
            // Добавляем тему только если её каталог есть (иначе проскочим несуществующего родителя).
            if (self::themeExists($name)) {
                $chain[] = $name;
            }
            $name = self::manifest($name)['parent'];
            if ($name !== null) {
                $name = (string)$name;
            }
        }
        return $chain;
    }

    /**
     * Резолвит путь шаблона 'site/...' в реальный относительный путь
     * (приоритет: активная тема -> цепочка родителей -> аварийный 'wiki').
     * Вызывающий отвечает за добавление .php.
     */
    public static function resolve(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        // Резолвим только сайтовые шаблоны; остальное (admin/шared) не трогаем.
        if (!str_starts_with($path, 'site/')) {
            return $path;
        }

        $candidates = self::chain();
        foreach ($candidates as $theme) {
            $dir = self::THEME_ROOT . '/' . $theme . '/' . $path;
            if (is_file(TEMPLATES . DIRECTORY_SEPARATOR . $dir . '.php')
                || is_file(TEMPLATES . DIRECTORY_SEPARATOR . $dir)) {
                return $dir;
            }
        }

        // Указанной темы нет или файла нет в цепочке — аварийный фолбэк на wiki.
        $fallback = self::THEME_ROOT . '/' . self::DEFAULT_THEME . '/' . $path;
        if (is_file(TEMPLATES . DIRECTORY_SEPARATOR . $fallback . '.php')
            || is_file(TEMPLATES . DIRECTORY_SEPARATOR . $fallback)) {
            return $fallback;
        }

        // Реальный отсутствующий файл — вернём исходный путь, HLEB укажет ошибку.
        return $path;
    }

    /**
     * Файл существует в активной теме (или цепочке её родителей)?
     */
    public static function has(string $path): bool
    {
        $path = trim($path, '/');
        foreach (self::chain() as $theme) {
            $p = self::THEME_ROOT . '/' . $theme . '/' . $path;
            if (is_file(TEMPLATES . DIRECTORY_SEPARATOR . $p . '.php')
                || is_file(TEMPLATES . DIRECTORY_SEPARATOR . $p)) {
                return true;
            }
        }
        return false;
    }

    /**
     * CSS-файлы активной темы (свои + унаследованные от родителей, без дублей).
     *
     * @return string[] относительные url
     */
    public static function css(): array
    {
        $list = [];
        foreach (array_reverse(self::chain()) as $theme) {
            foreach ((array)self::manifest($theme)['css'] ?? [] as $css) {
                $list[] = '/' . ltrim($css, '/');
            }
        }
        return array_values(array_unique($list));
    }

    /**
     * JS-файлы активной темы (свои + унаследованные).
     *
     * @return string[]
     */
    public static function js(): array
    {
        $list = [];
        foreach (array_reverse(self::chain()) as $theme) {
            foreach ((array)self::manifest($theme)['js'] ?? [] as $js) {
                $list[] = '/' . ltrim($js, '/');
            }
        }
        return array_values(array_unique($list));
    }

    /**
     * Версия для cache-busting.
     */
    public static function version(): string
    {
        return (string)config('general', 'version');
    }

    /**
     * Публикует ассеты активной темы (themes/{name}/assets/**) в public/assets/**,
     * чтобы URL вида /assets/css/blog.css (из манифеста) отдавались и при preview,
     * и при статической сборке. Копирует только отсутствующие/изменённые файлы.
     */
    public static function publishAssets(): void
    {
        foreach (self::chain() as $theme) {
            $src = TEMPLATES . DIRECTORY_SEPARATOR . self::THEME_ROOT . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'assets';
            if (!is_dir($src)) {
                continue;
            }
            $dst = HLEB_PUBLIC_DIR . DIRECTORY_SEPARATOR . 'assets';
            self::copyDir($src, $dst);
        }
    }

    private static function copyDir(string $src, string $dst): void
    {
        $items = @scandir($src);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $item;
            $to   = $dst . DIRECTORY_SEPARATOR . $item;
            if (is_dir($from)) {
                if (!is_dir($to)) {
                    @mkdir($to, 0775, true);
                }
                self::copyDir($from, $to);
            } elseif (is_file($from)) {
                if (!is_file($to) || @filemtime($from) > @filemtime($to)) {
                    @copy($from, $to);
                }
            }
        }
    }
}