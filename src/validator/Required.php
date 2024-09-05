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
    protected function validateAttribute(string $attribute)
    {
        if (!$this->model->hasAttribute($attribute)) {
            return;
        }

        $methods = $this->getPublicMethods();

        foreach ($methods as $method) {
            $result = call_user_func([$this, $method], $attribute);

            if (!$result) {
                $this->model->setError($attribute, $this->getMessage());
                return;
            }
        }
    }
}
