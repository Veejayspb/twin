<?php

namespace twin\validator;

use twin\common\SetPropertiesTrait;
use twin\model\Model;
use ReflectionClass;

abstract class Validator
{
    use SetPropertiesTrait;

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
     * Разрешить NULL в качестве значения.
     * @var bool
     */
    protected $null = false;

    /**
     * @param Model $model - валидируемая модель
     * @param array|string $attributes - валидируемый атрибут или атрибуты
     * @param array $params - значения свойств
     */
    public function __construct(Model $model, $attributes, array $params = [])
    {
        $this->setProperties($params);
        $this->model = $model;
        $this->attributes = (array)$attributes;
        $this->run();
    }

    /**
     * Запуск публичных методов валидации.
     * @return void
     */
    protected function run()
    {
        foreach ($this->attributes as $attribute) {
            $value = $this->model->$attribute;

            if ($value === '') {
                continue;
            }
            if ($this->null && $value === null) {
                continue;
            }
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
        foreach ($methods as $method) {
            $result = call_user_func([$this, $method], $this->model->$attribute, $attribute);
            if (!$result) {
                $this->model->setError($attribute, $this->message);
                return;
            }
        }
    }

    /**
     * Вернуть названия публичных нестатических методов.
     * @return array
     */
    private function getPublicMethods(): array
    {
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods();
        $result = [];
        foreach ($methods as $method) {
            if ($method->isPublic() && !$method->isStatic() && !$method->isConstructor()) {
                $result[] = $method->name;
            }
        }
        return $result;
    }
}
