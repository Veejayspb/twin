<?php

namespace twin\helper;

/**
 * Класс для хранения массива любых вспомогательных данных.
 *
 * $params = new Params;
 * $params->one = 1;
 * echo $params->one; // 1
 *
 * Class Params
 */
class Params
{
    /**
     * Массив параметров.
     * @var array
     */
    protected array $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->data[$name] = $value;
    }
}
