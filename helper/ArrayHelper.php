<?php

namespace twin\helper;

class ArrayHelper
{
    /**
     * Формирование нового массива на базе исходного.
     * @param array $array - исходный массив
     * @param callable $valueCallback - коллбэк для формирования значения: function($value, $key) {...}
     * @param callable|null $keyCallback - коллбэк для формирования ключа. Если NULL, то будет взят исходный ключ.
     * @return array
     */
    public static function column(array $array, callable $valueCallback, callable $keyCallback = null): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $k = $keyCallback === null ? $key : $keyCallback($value, $key);
            $v = $valueCallback($value, $key);
            $result[$k] = $v;
        }
        return $result;
    }

    /**
     * Найти в наборе массивов искомые ключ/значение, и вернуть индекс первого подходящего.
     * @param array $items - набор массивов
     * @param array $search - искомые ключи и значения
     * @return int|string|bool - FALSE, если индекс не найден
     */
    public static function findByParams(array $items, array $search)
    {
        foreach ($items as $i => $item) {
            $item = (array)$item;
            foreach ($search as $key => $value) {
                if (!isset($item[$key])) continue 2;
                if ($value !== $item[$key]) continue 2;
            }
            return $i;
        }
        return false;
    }

    /**
     * Представить массив данных в виде строки.
     * @param array $data - массив данных
     * @param callable $callback - коллбэк-функция с параметрами $key, $value, которая обрабатывает данные
     * @param string $glue - соединительная строка
     * @return string
     */
    public static function stringExpression(array $data, callable $callback, string $glue): string
    {
        $items = array_map($callback, array_keys($data), $data);
        return implode($glue, $items);
    }
}
