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
        $label = $this->form->getLabel($attribute);
        $this->setMessage("$label не является строкой");
        return 'string' == gettype($this->form->$attribute);
    }

    /**
     * Является ли датой.
     * @param string $attribute
     * @return bool
     */
    public function date(string $attribute): bool
    {
        $label = $this->form->getLabel($attribute);
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})$/';
        $this->setMessage("$label не является датой");

        if (!preg_match($pattern, $this->form->$attribute, $matches)) {
            return false;
        }

        return checkdate($matches[2], $matches[3], $matches[1]);
    }
}
