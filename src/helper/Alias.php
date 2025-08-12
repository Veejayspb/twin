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
    protected static array $aliases = [];

    /**
     * Установить значение алиаса.
     * @param string $alias - "@alias"
     * @param string $path - path/to/dir
     * @return bool
     */
    public static function set(string $alias, string $path): bool
    {
        $pattern = '/^' . self::PATTERN . '$/';

        if (!preg_match($pattern, $alias)) {
            return false;
        }

        // Алиас не должен ссылаться на самого себя
        if ($alias == substr($path, 0, strlen($alias))) {
            return false;
        }

        self::$aliases[$alias] = $path;
        return true;
    }

    /**
     * Проверка на сущ-ие указанного алиаса.
     * @param string $alias
     * @return bool
     */
    public static function isset(string $alias): bool
    {
        return array_key_exists($alias, self::$aliases);
    }

    /**
     * Удаление алиаса.
     * @param string $alias
     * @return void
     */
    public static function unset(string $alias): void
    {
        if (self::isset($alias)) {
            unset(self::$aliases[$alias]);
        }
    }

    /**
     * Вернуть значение алиаса.
     * @param string $alias - "@alias/path"
     * @return string - path/to/dir
     */
    public static function get(string $alias): string
    {
        $pattern = '/^' . self::PATTERN . '/';
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
            return self::get($result);
        }

        return $result;
    }
}
