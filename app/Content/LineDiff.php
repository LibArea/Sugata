<?php

declare(strict_types=1);

/**
 * Сравнивает два многострочных текста и возвращает массив операций:
 * каждая строка — ['op' => '=', 'text' => ...] | ['op' => '-', ...] | ['op' => '+', ...].
 *
 * Алгоритм: LCS (Longest Common Subsequence) с хешированием строк.
 * Для типичных объёмов markdown-статей достаточно эффективен.
 */
class LineDiff
{
    /**
     * @param string $oldStr исходный текст
     * @param string $newStr новый текст
     * @return array список ['op' => '='|'-'|'+', 'text' => string]
     */
    public static function diff(string $oldStr, string $newStr): array
    {
        $old = self::splitLines($oldStr);
        $new = self::splitLines($newStr);

        return self::compute($old, $new);
    }

    /**
     * Разбить текст на строки, сохранив пустые строки.
     */
    protected static function splitLines(string $text): array
    {
        if ($text === '') {
            return [];
        }
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));

        return $lines;
    }

    /**
     * Построчный diff через LCS.
     */
    protected static function compute(array $old, array $new): array
    {
        $n = count($old);
        $m = count($new);

        // Хешируем строки для быстрого сравнения (ключи — текст)
        $oldHashes = array_map('self::hashLine', $old);
        $newHashes = array_map('self::hashLine', $new);

        // Большие матрицы не строим целиком: используем динамику по строкам,
        // храня предыдущий ряд. Затем восстанавливаем операции backtrack'ом — но
        // backtrack требует (n+1)x(m+1). Для markdown статей это ок (обычно сотни строк).
        $nR = $n + 1;
        $mC = $m + 1;
        $dp = array_fill(0, $nR, null);
        for ($i = 0; $i < $nR; $i++) {
            $dp[$i] = array_fill(0, $mC, 0);
        }

        for ($i = 1; $i < $nR; $i++) {
            for ($j = 1; $j < $mC; $j++) {
                if ($oldHashes[$i - 1] === $newHashes[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }

        // Восстановление пути (backtrack).
        $result = [];
        $i = $n;
        $j = $m;
        while ($i > 0 && $j > 0) {
            if ($oldHashes[$i - 1] === $newHashes[$j - 1]) {
                $result[] = ['op' => '=', 'text' => $old[$i - 1]];
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] >= $dp[$i][$j - 1]) {
                $result[] = ['op' => '-', 'text' => $old[$i - 1]];
                $i--;
            } else {
                $result[] = ['op' => '+', 'text' => $new[$j - 1]];
                $j--;
            }
        }
        while ($i > 0) {
            $result[] = ['op' => '-', 'text' => $old[$i - 1]];
            $i--;
        }
        while ($j > 0) {
            $result[] = ['op' => '+', 'text' => $new[$j - 1]];
            $j--;
        }

        // Разворот (шли от конца).
        $result = array_reverse($result);

        // Схлопываем соседние одинаковые операции (опционально, для читаемости клиентской части).
        return $result;
    }

    protected static function hashLine(string $line): string
    {
        return md5($line);
    }
}