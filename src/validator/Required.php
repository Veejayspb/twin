<?php

namespace twin\validator;

class Required extends Validator
{
    /**
     * Заполнено ли значение атрибута.
     * @param string $attribute
     * @return bool
     */
    public function notEmpty(string $attribute): bool
    {
        $this->setMessage('Является обязательным атрибутом');
        return !static::isEmpty($this->model->$attribute);
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
                $this->model->error()->setError($attribute, $this->getMessage());
                return;
            }
        }
    }
}
