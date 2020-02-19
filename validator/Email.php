<?php

namespace twin\validator;

class Email extends Str
{
    /**
     * Корректен ли адрес.
     * @param $value
     * @param string $label
     * @return bool
     */
    public function email($value, string $label): bool
    {
        $this->message = "Некорректный email-адрес";
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
