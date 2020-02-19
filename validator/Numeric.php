<?php

namespace twin\validator;

abstract class Numeric extends Validator
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
     * Диапазон конкретных значений.
     * @var array
     */
    public $range = [];

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

    /**
     * Входит ли в диапазон конкретных значений.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function range($value, string $label): bool
    {
        if (empty($this->range)) return true;
        $this->message = "$label не входит в диапазон допустимых значений";
        return in_array($value, $this->range ,true);
    }
}
