<?php

namespace twin\validator;

abstract class Range extends Validator
{
    /**
     * Диапазон конкретных значений.
     * @var array
     */
    public $range = [];

    /**
     * Входит ли в диапазон конкретных значений.
     * @param string $attribute
     * @return bool
     */
    public function range(string $attribute): bool
    {
        if (empty($this->range)) {
            return true;
        }

        $this->setMessage("$attribute не входит в диапазон допустимых значений"); // TODO: использовать label вместо названия атрибута
        return in_array($this->form->$attribute, $this->range);
    }
}
