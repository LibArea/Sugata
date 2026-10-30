<?php

declare(strict_types=1);

/**
 * Получение численного значения параметра Яндекс ИКС (ТИЦ) с картинки.
 *
 * URI: https://a-panov.ru/massovaya-proverka-iks-skript-na-php/
 * Version: 1.1
 * Author: Andi Panov <a@a-panov.ru>
 * License: GPLv2
 */

namespace App\Support;

class YandexIks
{
    private static $reference = [
        '..xxxxxx....xxxxxxxxx.xxxxxxxxxxxxx.......xxxx.......xxxxx.....xxxxxxxxxxxxxx.xxxxxxxx..',
        '..xx........xxx.......xxx........xxxxxxxxxxxxxxxxxxxxxx',
        '.........xxxx......xxxxx......xxxxx.....xxxxxx...xxxx.xxxx.xxxx..xxxxxxxx...x.xxxx.....x',
        'x........xxxx.......xxxx...x...xxxx...x...xxxx..xxx..xxxxxxxxxxxxxxxxxxxxxxxx......xxxx.',
        '......xx.......xxxx......xxxxx....xxxx..x...xxxx...x...xxxxxxxxxxxxxxxxxxxxxx.......x...',
        '..........xxxxxxx...xxxxxxxx...xxx...xx...xxx...xx...xxx...xxxxxxxx...xxxxxxx.....xxxx..',
        '....xxxxx....xxxxxxxxx.xxxxxx.xxxxxxxxx...xxxxx.xx...xxxx..xx...xxx...xxxxxxxx....xxxxx.',
        'x..........x.........xx.......xxxx....xxxxxxx..xxxxxx..xxxxxxx....xxxxx......xxx........',
        '......xxxx.xxxxxxxxxxxxxxxxxxxxxxxx..xxx..xxxx..xxx..xxxxxxxxx..xxxxxxxxxxxxx.xxxx.xxxx.',
        '.xxxxx.....xxxxxxx...xxxx.xxx...xxx...xx..xxxx...xx.xxxxxx..xxxxx.xxxxxxxxx...xxxxxxx...',
    ];

    public static function getValueFromImage($domain)
    {
        $yaurl = 'https://www.yandex.ru/cycounter?' . $domain;
        $img = @imagecreatefrompng($yaurl);

        return $img ? self::parseImg($img) : '-';
    }

    private static function parseImg(&$src_img)
    {
        $iks_x = 30;
        $iks_y = 10;
        $iks_w = 52;
        $iks_h = 11;

        $dst_img = imagecreatetruecolor($iks_w, $iks_h);

        imagecopy($dst_img, $src_img, 0, 0, $iks_x, $iks_y, $iks_w, $iks_h);

        $arr = [];
        for ($i = 0; $i < $iks_w; ++$i) {
            $arr[$i] = '';
            for ($j = 0; $j < $iks_h; ++$j) {
                $arr[$i] .= 5855577 == imagecolorat($dst_img, $i, $j) ? '.' : 'x';
            }
        }

        for ($i = 0; $i < $iks_w; ++$i) {
            if ('...........' == $arr[$i]) {
                unset($arr[$i]);
            }
        }

        $iks = '';
        $current_symbol = '';
        $current_len = 0;

        foreach ($arr as $v) {
            $current_symbol .= $v;
            $current_len += 11;

            if (88 == $current_len) {
                foreach (self::$reference as $num => $symb) {
                    if (similar_text($symb, $current_symbol) > 80) {
                        $iks .= $num;
                        break;
                    }
                }
                $current_symbol = '';
                $current_len = 0;
            } elseif (55 == $current_len) {
                if (similar_text(self::$reference[1], $current_symbol) > 50) {
                    $iks .= 1;
                    $current_symbol = '';
                    $current_len = 0;
                }
            }
        }
        return $iks;
    }
}