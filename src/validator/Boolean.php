<?php

namespace twin\validator;

class Boolean extends Validator
{
    const ALLOWED_VALUES = [
        true,
        false,
        0,
        1,
        '0',
        '1',
    ];

    /**
     * Проверка типа.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $value = $this->form->$attribute;
        $this->setMessage('Должно равняться TRUE или FALSE');

        if (!in_array($value, static::ALLOWED_VALUES, true)) {
            return false;
        }

        $this->form->$attribute = (bool)$value;
        return true;
    }
}
