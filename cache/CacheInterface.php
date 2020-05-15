<?php

namespace twin\cache;

interface CacheInterface
{
    /**
     * Получить информацию из актуального кеша.
     * @param string $key - ключ
     * @return mixed|bool - FALSE в случае отсутствия кеша
     */
    public function get(string $key);

    /**
     * Кешировать информацию.
     * @param string $key - ключ
     * @param mixed $value - значение
     * @param int $ttl - время жизни в секундах (Time To Live)
     * @return bool
     */
    public function set(string $key, $value, int $ttl): bool;

    /**
     * Имеется ли актуальный кеш.
     * @param string $key - ключ
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Дата истечения жизни кеша.
     * @param string $key - ключ
     * @return int - 0, если кеш не существует или время жизни не ограничено
     */
    public function expires(string $key): int;
}
