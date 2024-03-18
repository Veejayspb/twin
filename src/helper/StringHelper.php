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
        $encoding = 'utf-8';
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $rest = mb_substr($string, 1, null, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $rest;
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

        if (count($variants) != 3) {
            return '';
        }

        if ($value % 100 > 4 && $value % 100 < 20) {
            $index = 2;
        } else {
            $cases = [2, 0, 1, 1, 1, 2];
            $index = $cases[min($value % 10, 5)];
        }

        return str_replace('#', $num, $variants[$index]);
    }

    /**
     * Извлечь расширение из названия файла.
     * @param string $name - название или путь до файла
     * @return string|null
     */
    public static function getExtFromName(string $name): ?string
    {
        return pathinfo($name, PATHINFO_EXTENSION) ?: null;
    }

    /**
     * Определить является ли атрибут объекта сервисным.
     * _attribute - сервисный
     *  attribute - обычный
     * @param string $name - название атрибута
     * @return bool
     */
    public static function isServiceAttribute(string $name): bool
    {
        return substr($name, 0, 1) == '_';
    }
}
