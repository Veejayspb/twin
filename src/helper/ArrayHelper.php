<?php

namespace twin\helper;

class ArrayHelper
{
    /**
     * Формирование нового массива на базе исходного.
     * @param array $array - исходный массив
     * @param callable $keyCallback - коллбэк для формирования ключа: function($key, $value) {...}
     * @param callable $valueCallback - коллбэк для формирования значения: function($key, $value) {...}
     * @return array
     */
    public static function column(array $array, callable $keyCallback, callable $valueCallback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $k = $keyCallback($key, $value);
            $v = $valueCallback($key, $value);

            $result[$k] = $v;
        }

        return $result;
    }

    /**
     * Проверка массива на существование указанных элементов.
     * Проверяются как значения, так и ключи. Строгое сравнение типов.
     * @param array $array - исходный массив
     * @param array $elements - массив элементов, которые должны содержаться в исходном массиве
     * @return bool
     */
    public static function hasElements(array $array, array $elements): bool
    {
        foreach ($elements as $key => $value) {
            if (!array_key_exists($key, $array) || $array[$key] !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Найти в наборе массивов искомые ключ/значение, и вернуть индекс первого подходящего.
     * @param array $items - набор массивов или объектов
     * @param array $search - искомые ключи и значения
     * @return int|string|bool - FALSE, если индекс не найден
     */
    public static function findByParams(array $items, array $search)
    {
        foreach ($items as $i => $item) {
            $item = (array)$item;
            $hasElements = static::hasElements($item, $search);

            if ($hasElements) {
                return $i;
            }
        }

        return false;
    }

    /**
     * Представить массив данных в виде строки.
     * @param array $data - массив данных
     * @param callable $callback - коллбэк-функция, которая обрабатывает данные: function($key, $value) {...}
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
     * @param array $array_2 - массив с дополнительными данными
     * @return array
     * @see array_merge_recursive()
     */
    public static function merge(array $array_1, array $array_2): array
    {
        foreach ($array_2 as $key => $value) {

            if (!array_key_exists($key, $array_1)) {
                $array_1[$key] = $value;
                continue;
            }

            if (is_array($array_1[$key]) && is_array($value)) {
                $array_1[$key] = static::merge($array_1[$key], $value);
            }
        }

        return $array_1;
    }

    /**
     * Проверка на существование указанных ключей в массиве.
     * @param array $keys - список ключей (строгое сравнение)
     * @param array $array - исходный массив
     * @param bool $only - массив должен состоять только из указанных ключей (иных быть не должно)
     * @return bool
     */
    public static function keysExist(array $keys, array $array, bool $only = false): bool
    {
        $keys = array_unique($keys);

        if ($only && count($keys) != count($array)) {
            return false;
        }

        foreach ($keys as $key) {
            $type = gettype($key);

            if (
                !in_array($type, ['integer', 'string']) ||
                !array_key_exists($key, $array)
            ) {
                return false;
            }
        }

        return true;
    }
}
