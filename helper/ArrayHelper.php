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

    /**
     * Рекурсивное слияние 2х массивов.
     * Отличие от функции array_merge_recursive() в том, что 1-ый массив приоритетнее 2-го.
     * Параметры из 2-го просто дополняют 1-ый, но не заменяют в нем ничего.
     * @param array $array_1 - главный массив
     * @param array $array_2 - массив с дополнительным данными
     * @return array
     * @see array_merge_recursive()
     */
    public static function merge(array $array_1, array $array_2): array
    {
        foreach ($array_2 as $key => $value) {
            // Если ключ в 1 массиве отсутствует, то сразу заполняем его
            if (!array_key_exists($key, $array_1)) {
                $array_1[$key] = $value;
            }

            // Замена значения
            if (!is_array($array_1[$key]) && !is_array($value)) {
                $array_1[$key] = $value;
            }

            // Если значения из 1 и 2 массивов, совпадающие по ключам являются массивами, то продолжить рекурсивное слияние.
            if (is_array($array_1[$key]) && is_array($value)) {
                $array_1[$key] = static::merge($array_1[$key], $array_2[$key]);
            }
        }
        return $array_1;
    }
}
