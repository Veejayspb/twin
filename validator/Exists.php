<?php

namespace twin\validator;

class Exists extends Validator
{
    /**
     * Название модели, в которой осуществлять поиск зависимости.
     * @var string
     */
    public $class;

    /**
     * Название столбца, по которому определять зависимость.
     * @var string
     */
    public $column = 'id';

    /**
     * Имеется ли родительская запись.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function exists($value, string $label): bool
    {
        $this->message = "Родительская запись не найдена";
        return (bool)($this->class)::findByAttributes([
            $this->column => $value,
        ])->one();
    }
}
