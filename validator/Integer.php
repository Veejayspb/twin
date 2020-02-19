<?php

namespace twin\validator;

class Integer extends Numeric
{
    /**
     * Проверка типа.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function type($value, string $label): bool
    {
        $this->message = "$label не является целым числом";
        return 'integer' == gettype($value);
    }
}
