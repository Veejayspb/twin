<?php

namespace twin\validator;

class Boolean extends Validator
{
    /**
     * Проверка типа.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function type($value, string $attribute): bool
    {
        $this->message = 'Должно равняться 0 или 1';
        return in_array($value, [true, false, 0, 1], true);
    }
}
