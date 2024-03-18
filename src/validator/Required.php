<?php

namespace twin\validator;

class Required extends Validator
{
    /**
     * {@inheritdoc}
     */
    public $message = 'Является обязательным атрибутом';

    /**
     * Заполнено ли значение атрибута.
     * @param string $attribute
     * @return bool
     */
    public function notEmpty(string $attribute): bool
    {
        return !in_array($this->model->$attribute, [null, ''], true);
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
