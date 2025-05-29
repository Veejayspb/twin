<?php

namespace twin\cache;

final class CacheItem
{
    /**
     * Ключ.
     * @var string
     */
    public string $key;

    /**
     * Данные кеша.
     * @var mixed
     */
    public mixed $data;

    /**
     * Дата истечения кеша.
     * @var int
     */
    public int $expires = 0;

    /**
     * @param array $properties - свойства объекта
     */
    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }

    /**
     * Свойства объекта в формате JSON.
     * @return string
     */
    public function __toString()
    {
        $data = [
            'data' => $this->data,
            'expires' => $this->expires,
        ];
        return json_encode($data);
    }

    /**
     * Установить значения свойств.
     * @param array $properties - свойства
     * @return static
     */
    public function setProperties(array $properties): self
    {
        foreach ($properties as $key => $value) {
            if (!property_exists($this, $key)) continue;
            $this->$key = array_key_exists($key, $properties) ? $value : $this->data;
        }
        return $this;
    }

    /**
     * Истекло ли время хранения кэша.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires < time();
    }

    /**
     * Вернуть хэш ключа.
     * @return string
     */
    public function getHash(): string
    {
        return md5($this->key);
    }
}
