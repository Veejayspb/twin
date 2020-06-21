<?php

namespace twin\validator;

class Boolean extends Validator
{
    /**
     * Проверка типа.
     * @param mixed $value
     * @param string $label
     * @param string $attribute
     * @return bool
     */
    public function type($value, string $label, string $attribute): bool
    {
        $this->message = 'Должно равняться 0 или 1';
        return $value == 1 || $value == 0;
    }
}
