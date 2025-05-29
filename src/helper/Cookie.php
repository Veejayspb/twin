<?php

namespace twin\helper;

class Cookie
{
    /**
     * Префикс параметров.
     */
    const NAME_PREFIX = 'c_';

    /**
     * Сохранить параметр в куки.
     * @param string $name - название параметра
     * @param string $value - значение
     * @param int $expire - кол-во секунд, через которое истечет срок действия
     * @param string $path - путь к директории на сервере, из которой будут доступны куки
     * @param string $domain - домен
     * @param bool $secure
     * @param bool $httpOnly
     * @return bool
     */
    public static function set(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): bool
    {
        $name = static::getName($name);

        if ($expire != 0) {
            $expire+= time();
        }

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Вернуть параметр из куки.
     * @param string $name - название параметра
     * @param mixed|null $default - значение по-умолчанию
     * @return string|null
     */
    public static function get(string $name, mixed $default = null): ?string
    {
        $name = static::getName($name);
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Удалить параметр из куки.
     * @param string $name - название параметра
     * @return bool
     */
    public static function delete(string $name): bool
    {
        $name = static::getName($name);

        if (array_key_exists($name, $_COOKIE)) {
            unset($_COOKIE[$name]);
            return setcookie($name, null, -1, '/');
        }

        return true;
    }

    /**
     * Вернуть название параметра с префиксом.
     * @param string $name - название параметра
     * @return string
     */
    private static function getName(string $name): string
    {
        return static::NAME_PREFIX . $name;
    }
}
