<?php

namespace twin\validator;

class Date extends Validator
{
    /**
     * Является ли строкой.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function type($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);
        $this->message = "$label не является строкой";
        return 'string' == gettype($value);
    }

    /**
     * Является ли датой.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function date($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})$/';
        $this->message = "$label не является датой";

        if (!preg_match($pattern, $value, $matches)) {
            return false;
        }

        return checkdate($matches[2], $matches[3], $matches[1]);
    }
}
