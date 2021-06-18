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
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function type($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);
        $this->message = "$label не является строкой";
        return 'string' == gettype($value);
    }

    /**
     * Длина меньше минимальной.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function min($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->min === null) {
            return true;
        }
        $this->message = "Длина поля \"$label\" должна быть больше или равна $this->min";
        return $this->min <= mb_strlen($value);
    }

    /**
     * Длина больше максимальной.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function max($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->max === null) {
            return true;
        }
        $this->message = "Длина $label должна быть меньше или равна $this->max";
        return mb_strlen($value) <= $this->max;
    }

    /**
     * Соответствует ли регулярному выражению.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function pattern($value, string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);

        if ($this->pattern === null) {
            return true;
        }
        $this->message = "$label не соответствует шаблону";
        return preg_match($this->pattern, $value);
    }
}
