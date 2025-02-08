<?php

namespace twin\validator;

class Integer extends Numeric
{
    /**
     * Проверка типа.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $value = $this->model->$attribute;
        $this->setMessage("$attribute не является целым числом"); // TODO: использовать label вместо названия атрибута
        $type = gettype($value);

        if ($type == 'integer') {
            return true;
        }

        if ($type == 'string' && preg_match('/^-?[0-9]+$/', $value)) {
            $this->model->$attribute = (int)$value;
            return true;
        }

        return false;
    }
}
