<?php

namespace twin\validator;

class Date extends Validator
{
    /**
     * Является ли строкой.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $this->setMessage("$attribute не является строкой"); // TODO: использовать label вместо названия атрибута
        return 'string' == gettype($this->form->$attribute);
    }

    /**
     * Является ли датой.
     * @param string $attribute
     * @return bool
     */
    public function date(string $attribute): bool
    {
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})$/';
        $this->setMessage("$attribute не является датой"); // TODO: использовать label вместо названия атрибута

        if (!preg_match($pattern, $this->form->$attribute, $matches)) {
            return false;
        }

        return checkdate($matches[2], $matches[3], $matches[1]);
    }
}
