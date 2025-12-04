<?php

declare(strict_types=1);

namespace App\Controllers;

use Hleb\Base\Module;
use MatthiasMullie\Minify;

class SassController extends Module
{
    public function collect(): void
    {
        $this->buildCss(HLEB_GLOBAL_DIR . '/resources/views/assets/css/build.css', 'style');

        // Generic js
        foreach (config('general', 'path_js') as $key => $putch) {
            $this->buildJs(HLEB_GLOBAL_DIR . $putch, $key);
        }

        // Separate style files that may not be included in the templates (example: rtl.css)
        // Отдельные файлы стилей, которые могут не войти в шаблоны (пример: rtl.css)
        foreach (config('general', 'path_css') as $key => $putch) {
            $this->buildCss(HLEB_GLOBAL_DIR . $putch, $key);
        }
    }

    protected function buildCss($putch, $key)
    {
        $minifier = new Minify\CSS($putch);
        $minifier->minify(HLEB_PUBLIC_DIR . '/assets/css/' . $key . '.css');

        return true;
    }

    protected function buildJs($putch, $key)
    {
        $minifier = new Minify\JS($putch . $key . '.js');
        $minifier->minify(HLEB_PUBLIC_DIR . '/assets/js/' . $key . '.js');

        return true;
    }
}
