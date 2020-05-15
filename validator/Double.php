<?php

namespace twin\validator;

class Double extends Numeric
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
        $this->message = "$label не является числом";
        $type = gettype($value);
        if ($type == 'integer' || $type == 'double') return true;
        if ($type == 'string' && preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $value)) {
            $this->model->$attribute = (double)$value;
            return true;
        }
        return false;
    }
}
