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
        $value = $this->form->$attribute;
        $this->setMessage("$attribute не является числом"); // TODO: использовать label вместо названия атрибута
        $type = gettype($value);

        if ($type == 'integer' || $type == 'double') {
            return true;
        }

        if ($type == 'string' && preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $value)) {
            $this->form->$attribute = (double)$value;
            return true;
        }

        return false;
    }
}
