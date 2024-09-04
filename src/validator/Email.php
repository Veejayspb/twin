<?php

namespace twin\validator;

class Email extends Str
{
    /**
     * Корректен ли адрес.
     * @param string $attribute
     * @return bool
     */
    public function email(string $attribute): bool
    {
        $this->setMessage('Некорректный email-адрес');
        $pattern = "/^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/u";
        return preg_match($pattern, $this->model->$attribute, $matches);
    }
}
