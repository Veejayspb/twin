<?php

namespace twin\cache;

use twin\common\Component;

abstract class Cache extends Component implements CacheInterface
{
    /**
     * Массив объектов с данными из кеша.
     * @var CacheItem[]
     */
    protected $items = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        $item = $this->getItem($key);
        if ($item === false) return false;
        if ($item->isExpired()) return false;
        return $item->data;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, int $ttl): bool
    {
        $item = new CacheItem();
        $item->key = $key;
        $item->data = $value;
        $item->expires = $ttl + time();
        $this->items[$key] = $item;

        $result = $this->saveItem($item);
        if ($result) {
            $this->items[$item->key] = $item;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $key): bool
    {
        $item = $this->getItem($key);
        if ($item === false) return false;
        return !$item->isExpired();
    }

    /**
     * {@inheritdoc}
     */
    public function expires(string $key): int
    {
        $item = $this->getItem($key);
        if ($item === false) return 0;
        return $item->expires - time();
    }

    /**
     * Вернуть объект с данными кеша.
     * @param string $key - ключ
     * @return CacheItem|bool - FALSE в случае ошибки
     */
    protected function getItem(string $key)
    {
        if (!array_key_exists($key, $this->items)) {
            $this->items[$key] = $this->extractItem($key);
        }
        return $this->items[$key];
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
