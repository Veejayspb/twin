<?php

namespace twin\validator;

class Email extends Str
{
    /**
     * Корректен ли адрес.
     * @param mixed $value
     * @return bool
     */
    public function email($value): bool
    {
        $this->message = "Некорректный email-адрес";
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}
