<?php

namespace twin\cache;

use twin\common\Component;

abstract class Cache extends Component
{
    /**
     * Получить информацию из актуального кеша.
     * @param string $key - ключ
     * @param mixed|null $default - значение по-умолчанию в случае отсутствия кеша
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        $item = $this->extractItem($key);
        if ($item === false) return $default;
        if ($item->isExpired()) return $default;
        return $item->data;
    }

    /**
     * Кешировать информацию.
     * @param string $key - ключ
     * @param mixed $value - значение
     * @param int $ttl - время жизни в секундах (Time To Live)
     * @return bool
     */
    public function set(string $key, $value, int $ttl): bool
    {
        $item = new CacheItem;
        $item->key = $key;
        $item->data = $value;
        $item->expires = $ttl + time();

        return $this->saveItem($item);
    }

    /**
     * Имеется ли актуальный кеш.
     * @param string $key - ключ
     * @return bool
     */
    public function exists(string $key): bool
    {
        $item = $this->extractItem($key);
        if ($item === false) return false;
        return !$item->isExpired();
    }

    /**
     * Дата истечения жизни кеша.
     * @param string $key - ключ
     * @return int - 0, если кеш не существует или время жизни не ограничено
     */
    public function expires(string $key): int
    {
        $item = $this->extractItem($key);
        if ($item === false) return 0;
        return $item->expires - time();
    }

    /**
     * Извлечь объект с данными кеша из хранилища.
     * @param string $key - ключ
     * @return CacheItem|bool - FALSE в случае ошибки
     */
    abstract protected function extractItem(string $key);

    /**
     * Сохранить объект с данными кеша в хранилище.
     * @param CacheItem $item - объект с данными кеша
     * @return bool
     */
    abstract protected function saveItem(CacheItem $item): bool;
}
