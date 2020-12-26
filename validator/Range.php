<?php

namespace twin\validator;

abstract class Range extends Validator
{
    /**
     * Диапазон конкретных значений.
     * @var array
     */
    public $range = [];

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
        return in_array($value, $this->range);
    }
}
