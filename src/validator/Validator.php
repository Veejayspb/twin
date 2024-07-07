<?php

namespace twin\validator;

use twin\helper\ObjectHelper;
use twin\model\Model;
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
    public $message = 'Ошибка валидации';

    /**
     * Разрешить NULL в качестве значения.
     * @var bool
     */
    public $null = false;

    /**
     * @param Model $model - валидируемая модель
     * @param array $attributes - валидируемые атрибуты
     * @param array $properties - значения свойств
     */
    public function __construct(Model $model, array $attributes, array $properties = [])
    {
        (new ObjectHelper($this))->setProperties($properties);

        $this->model = $model;
        $this->attributes = $attributes;
        $this->run();
    }

    /**
     * Запуск публичных методов валидации.
     * @return void
     */
    protected function run()
    {
        foreach ($this->attributes as $attribute) {
            if (!$this->model->hasAttribute($attribute)) {
                continue;
            }

            $value = $this->model->getAttribute($attribute);

            if ($value === '') {
                continue;
            }

            if ($this->null === true && $value === null) {
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
            $result = call_user_func([$this, $method], $attribute);

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
