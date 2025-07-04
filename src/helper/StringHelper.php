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
     * Преобразовать стиль названия из CAMEL в KABOB.
     * Допустимы только символы: A-Z, a-z, 0-9
     * @param string $str - SomeName
     * @return string - some-name
     */
    public static function camelToKabob(string $str): string
    {
        $str = preg_replace('/[^A-Za-z0-9]/', '', $str);
        $str = preg_replace('/[A-Z]/', '-$0', $str);
        $str = mb_strtolower($str, 'utf-8');
        return trim($str, '-');
    }

    /**
     * Преобразовать стиль названия из KABOB в CAMEL.
     * Допустимы только символы: a-z, 0-9, -
     * @param string $str - some-name
     * @return string - SomeName
     */
    public static function kabobToCamel(string $str): string
    {
        $str = mb_strtolower($str, 'utf-8');
        $str = preg_replace('/[^a-z0-9\-]/', '', $str);
        $parts = explode('-', $str);

        $parts = array_map(function ($part) {
            return ucfirst($part);
        }, $parts);

        return implode('', $parts);
    }

    /**
     * Транслитерация строки.
     * @param string $str
     * @return string
     */
    public static function slug(string $str): string
    {
        $converter = [
            'а' => 'a',  'б' => 'b',  'в' => 'v',
            'г' => 'g',  'д' => 'd',  'е' => 'e',
            'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i',  'й' => 'j',  'к' => 'k',
            'л' => 'l',  'м' => 'm',  'н' => 'n',
            'о' => 'o',  'п' => 'p',  'р' => 'r',
            'с' => 's',  'т' => 't',  'у' => 'u',
            'ф' => 'f',  'х' => 'h',  'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '',   'ы' => 'y',  'ъ' => '',
            'э' => 'e',  'ю' => 'yu', 'я' => 'ya',
            ' ' => '_',
        ];

        $str = mb_strtolower($str);
        $str = strtr($str, $converter);
        return preg_replace('/[^a-z0-9_\-]+/i', '', $str);
    }
}
