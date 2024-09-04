<?php

namespace twin\validator;

class Required extends Validator
{
    /**
     * Значения, которые считаются пустыми.
     */
    const EMPTY_VALUES = [
        null,
        '',
        [],
    ];

    /**
     * Заполнено ли значение атрибута.
     * @param string $attribute
     * @return bool
     */
    public function notEmpty(string $attribute): bool
    {
        $this->setMessage('Является обязательным атрибутом');
        return !in_array($this->model->$attribute, static::EMPTY_VALUES, true);
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
