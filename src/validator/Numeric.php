<?php

namespace twin\validator;

abstract class Numeric extends Range
{
    /**
     * Минимальное значение.
     * @var int|null
     */
    public $min;

    /**
     * Максимальное значение.
     * @var int|null
     */
    public $max;

    /**
     * Ниже минимального значения.
     * @param string $attribute
     * @return bool
     */
    public function min(string $attribute): bool
    {
        if ($this->min === null) {
            return true;
        }

        $this->setMessage("$attribute должен быть больше или равен $this->min"); // TODO: использовать label вместо названия атрибута
        return $this->min <= $this->model->$attribute;
    }

    /**
     * Выше максимального значения.
     * @param string $attribute
     * @return bool
     */
    public function max(string $attribute): bool
    {
        if ($this->max === null) {
            return true;
        }

        $this->setMessage("$attribute должен быть меньше или равен $this->max"); // TODO: использовать label вместо названия атрибута
        return $this->model->$attribute <= $this->max;
    }
}
