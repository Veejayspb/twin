<?php

namespace twin\validator;

class Double extends Numeric
{
    /**
     * Проверка типа.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $value = $this->model->$attribute;
        $label = $this->model->getLabel($attribute);
        $this->message = "$label не является числом";
        $type = gettype($value);

        if ($type == 'integer' || $type == 'double') {
            return true;
        }

        if ($type == 'string' && preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $value)) {
            $this->model->$attribute = (double)$value;
            return true;
        }

        return false;
    }
}
