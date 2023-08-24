<?php

namespace twin\validator;

class Email extends Str
{
    /**
     * Корректен ли адрес.
     * @param mixed $value
     * @param string $attribute
     * @return bool
     */
    public function email($value, string $attribute): bool
    {
        $this->message = 'Некорректный email-адрес';
        $pattern = "/^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/u";
        return preg_match($pattern, $value, $matches);
    }
}
