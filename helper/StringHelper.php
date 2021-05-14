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
}
