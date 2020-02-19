<?php

namespace core\validator;

use core\model\Model;
use ReflectionClass;

abstract class Validator
{
    /**
     * Валидируемая модель.
     * @var Model
     */
    protected $model;

    /**
     * Валидируемые атрибуты.
     * @var array
     */
    protected $attributes;

    /**
     * Текст ошибки валидации.
     * @var string
     */
    protected $message = 'Ошибка валидации';

    /**
     * Разрешить пустое значение.
     * @var bool
     */
    protected $empty = true;

    /**
     * @param Model $model - валидируемая модель
     * @param array|string $attributes - валидируемый атрибут или атрибуты
     * @param array $params - значения свойств
     */
    public function __construct(Model $model, $attributes, array $params = [])
    {
        $this->model = $model;
        $this->attributes = (array)$attributes;
        $this->setParams($params);
        $this->run();
    }

    /**
     * Запуск публичных методов валидации.
     * @return void
     */
    protected function run()
    {
        foreach ($this->attributes as $attribute) {
            $this->validateAttribute($attribute);
        }
    }

    /**
     * Валидация атрибута.
     * @param string $attribute - название атрибута
     * @return void
     */
    protected function validateAttribute(string $attribute)
    {
        $methods = $this->getPublicMethods();
        if ($this->empty && empty($this->model->$attribute)) return;
        foreach ($methods as $method) {
            $result = call_user_func([$this, $method], $this->model->$attribute, $this->model->getLabel($attribute), $attribute);
            if (!$result) {
                $this->model->addError($attribute, $this->message);
                return;
            }
        }
    }

    /**
     * Вернуть названия публичных методов.
     * @return array
     */
    private function getPublicMethods(): array
    {
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods();
        $result = [];
        foreach ($methods as $method) {
            if ($method->isPublic() && !$method->isConstructor()) {
                $result[] = $method->name;
            }
        }
        return $result;
    }

    /**
     * Установить значения свойств.
     * @param array $params - свойства
     * @return void
     */
    private function setParams(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
