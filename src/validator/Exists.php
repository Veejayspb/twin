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
     * @param string $attribute
     * @return bool
     */
    public function exists(string $attribute): bool
    {
        $this->message = 'Родительская запись не найдена';

        return (bool)($this->class)::findByAttributes([
            $this->column => $this->model->$attribute,
        ])->one();
    }
}
