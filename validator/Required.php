<?php

namespace twin\validator;

class Required extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected $message = 'Является обязательным атрибутом';

    /**
     * Заполнено ли значение атрибута.
     * @param mixed $value
     * @param string $label
     * @param string $attribute
     * @return bool
     */
    public function notEmpty($value, string $label, string $attribute): bool
    {
        return !in_array($value, [null, ''], true);
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        foreach ($this->attributes as $attribute) {
            $this->validateAttribute($attribute);
        }
    }
}
