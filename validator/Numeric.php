<?php

namespace twin\validator;

abstract class Numeric extends Range
{
    /**
     * Минимальное значение.
     * @var int|null
     */
    public $min;

    /**
     * Максимальное значение.
     * @var int|null
     */
    public $max;

    /**
     * Ниже минимального значения.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function min($value, string $label): bool
    {
        if ($this->min === null) return true;
        $this->message = "$label должен быть больше или равен $this->min";
        return $this->min <= $value;
    }

    /**
     * Выше максимального значения.
     * @param int $value
     * @param string $label
     * @return bool
     */
    public function max($value, string $label): bool
    {
        if ($this->max === null) return true;
        $this->message = "$label должен быть меньше или равен $this->max";
        return $value <= $this->max;
    }
}
