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
        $label = $this->model->getLabel($attribute);
        $this->setMessage("$label не является строкой");
        return 'string' == gettype($this->model->$attribute);
    }

    /**
     * Является ли датой.
     * @param string $attribute
     * @return bool
     */
    public function date(string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})$/';
        $this->setMessage("$label не является датой");

        if (!preg_match($pattern, $this->model->$attribute, $matches)) {
            return false;
        }

        return checkdate($matches[2], $matches[3], $matches[1]);
    }
}
