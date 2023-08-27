<?php

namespace twin\validator;

class Boolean extends Validator
{
    /**
     * Проверка типа.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $this->message = 'Должно равняться 0 или 1';
        return in_array($this->model->$attribute, [true, false, 0, 1], true);
    }
}
