<?php

namespace twin\helper;

class StringHelper
{
    /**
     * Сделать первый символ строки заглавным.
     * @param string $string
     * @return string
     */
    public static function ucfirst(string $string): string
    {
        $firstChar = mb_substr($string, 0, 1, 'utf-8');
        $rest = mb_substr($string, 1, null, 'utf-8');
        return mb_strtoupper($firstChar, 'utf-8') . $rest;
    }

    /**
     * Указать корректную количественную форму слова.
     * @param int $num - кол-во
     * @param array $variants - [# единица, # единицы, # единиц]
     * @return string
     */
    public static function wordEnding(int $num, array $variants): string
    {
        $value = abs($num);
        if (count($variants) < 3) return '';
        $cases = [2, 0, 1, 1, 1, 2];

        if ($value % 100 > 4 && $value % 100 < 20) {
            $index = 2;
        } else {
            $index = $cases[min($value % 10, 5)];
        }

        $str = $variants[$index];
        $str = str_replace('#', '%d', $str);
        return sprintf($str, $num);
    }

    /**
     * Извлечь расширение из названия файла.
     * @param string $name - название или путь до файла
     * @return string|null
     */
    public static function getFileExt(string $name)
    {
        preg_match('/\.([a-z0-9]+)$/', $name, $matches);
        return $matches ? $matches[1] : null;
    }
}
