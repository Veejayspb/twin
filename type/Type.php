<?php

namespace twin\type;

use twin\model\Model;

abstract class Type
{
    /**
     * Модель.
     * @var Model
     */
    protected $model;

    /**
     * Название атрибута.
     * @var string
     */
    protected $attribute;

    /**
     * Вызванные объекты типов.
     * Мультитон.
     * @var static[]
     */
    protected static $instances = [];

    private function __construct() {}

    private function __clone() {}

    private function __wakeup() {}

    /**
     * Инстанцировать объект с типом (если еще не инстанцирован).
     * @param Model $model
     * @param string $attribute
     * @return static
     */
    public static function instance(Model $model, string $attribute): self
    {
        if (!array_key_exists(static::class, static::$instances)) {
            static::$instances[static::class] = new static;
        }
        $type = static::$instances[static::class];
        $type->model = $model;
        $type->attribute = $attribute;
        return $type;
    }

    /**
     * Метод, вызываемый при присвоении атрибута.
     * @param string $value - исходное значение, полученное из БД или других источников
     * @return mixed - обработанный результат
     * @see Model::setAttribute()
     */
    abstract public function set($value);

    /**
     * Метод, вызываемый при получении значения атрибута.
     * @return mixed - обработанный результат
     * @see Model::getAttribute()
     */
    public function get()
    {
        return $this->model->{$this->attribute};
    }
}
