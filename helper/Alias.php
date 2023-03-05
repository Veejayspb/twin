<?php

namespace twin\helper;

final class Alias
{
    /**
     * Паттерн алиаса.
     */
    const PATTERN = '@[a-z]+';

    /**
     * Список алиасов.
     * @var array
     */
    protected static $aliases = [];

    /**
     * Установить значение алиаса.
     * @param string $alias - "@alias"
     * @param string $path - path/to/dir
     * @return bool
     */
    public static function set(string $alias, string $path): bool
    {
        $pattern = '/^' . static::PATTERN . '$/';

        if (!preg_match($pattern, $alias)) {
            return false;
        }

        self::$aliases[$alias] = $path;
        return true;
    }

    /**
     * Вернуть значение алиаса.
     * @param string $alias - "@alias/path"
     * @return string - path/to/dir
     */
    public static function get(string $alias): string
    {
        $pattern = '/^' . static::PATTERN . '/';
        preg_match($pattern, $alias, $matches);

        if (!isset($matches[0])) {
            return $alias;
        }

        $key = $matches[0];

        if (!array_key_exists($key, self::$aliases)) {
            return $alias;
        }

        $result = str_replace($key, self::$aliases[$key], $alias);

        // Если в пути остался алиас, то выполнить повторное преобразование
        if (preg_match($pattern, $result)) {
            return static::get($result);
        }

        return $result;
    }
}
