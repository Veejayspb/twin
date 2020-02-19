<?php

namespace core\validator;

class Double extends Numeric
{
    /**
     * Проверка типа.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function type($value, string $label): bool
    {
        $this->message = "$label не является числом";
        $type = gettype($value);
        return $type == 'integer' || $type == 'double';
    }
}
