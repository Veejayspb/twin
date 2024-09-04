<?php

namespace twin\validator;

class Str extends Range
{
    /**
     * Минимальная длина
     * @var int|null
     */
    public $min;

    /**
     * Максимальная длина.
     * @var int|null
     */
    public $max;

    /**
     * Регулярное выражение для сравнения.
     * @var string|null
     */
    public $pattern;

    /**
     * Является ли строкой.
     * @param string $attribute
     * @return bool
     */
    public function type(string $attribute): bool
    {
        $value = $this->model->$attribute;
        $type = gettype($value);
        $label = $this->model->getLabel($attribute);
        $this->setMessage("$label не является строкой");

        if ($type == 'string') {
            return true;
        }

        if (in_array($type, ['integer', 'double'])) {
            $this->model->$attribute = (string)$value;
            return true;
        }

        return false;
    }

    /**
     * Длина меньше минимальной.
     * @param string $attribute
     * @return bool
     */
    public function min(string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->min === null) {
            return true;
        }

        $this->setMessage("Длина поля \"$label\" должна быть больше или равна $this->min");
        return $this->min <= mb_strlen($this->model->$attribute);
    }

    /**
     * Длина больше максимальной.
     * @param string $attribute
     * @return bool
     */
    public function max(string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->max === null) {
            return true;
        }

        $this->setMessage("Длина $label должна быть меньше или равна $this->max");
        return mb_strlen($this->model->$attribute) <= $this->max;
    }

    /**
     * Соответствует ли регулярному выражению.
     * @param string $attribute
     * @return bool
     */
    public function pattern(string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->pattern === null) {
            return true;
        }

        $this->setMessage("$label не соответствует шаблону");
        return preg_match($this->pattern, $this->model->$attribute);
    }
}
