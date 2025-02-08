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

        $label = $this->form->getLabel($attribute);
        $this->setMessage("$label должен быть больше или равен $this->min");
        return $this->min <= $this->form->$attribute;
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

        $label = $this->form->getLabel($attribute);
        $this->setMessage("$label должен быть меньше или равен $this->max");
        return $this->form->$attribute <= $this->max;
    }
}
