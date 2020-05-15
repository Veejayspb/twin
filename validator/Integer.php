<?php

namespace twin\validator;

class Integer extends Numeric
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
        $this->message = "$label не является целым числом";
        $type = gettype($value);
        if ($type == 'integer') return true;
        if ($type == 'string' && preg_match('/^-?[0-9]+$/', $value)) {
            $this->model->$attribute = (int)$value;
            return true;
        }
        return false;
    }
}
