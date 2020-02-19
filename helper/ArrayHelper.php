<?php

namespace twin\helper;

class ArrayHelper
{
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
