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
        $value = $this->model->$attribute;
        $this->message = 'Должно равняться TRUE или FALSE';

        if (!in_array($value, static::ALLOWED_VALUES, true)) {
            return false;
        }

        $this->model->$attribute = (bool)$value;
        return true;
    }
}
