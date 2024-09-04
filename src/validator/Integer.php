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
        $label = $this->model->getLabel($attribute);
        $this->setMessage("$label не является целым числом");
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
