<?php

namespace twin\validator;

use twin\helper\ObjectHelper;
use twin\model\Model;
use ReflectionClass;

abstract class Validator
{
    const DEFAULT_MESSAGE = 'Ошибка валидации';

    const EMPTY_VALUES = [
        null,
        '',
        [],
    ];

    /**
     * Валидируемая модель.
     * @var Model
     */
    protected Model $model;

    /**
     * Валидируемые атрибуты.
     * @var array
     */
    protected array $attributes;

    /**
     * Текст ошибки валидации.
     * @var string|null
     */
    public ?string $message;

    /**
     * Разрешить NULL в качестве значения.
     * @var bool
     */
    public bool $null = false;

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
    protected function run(): void
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
    protected function validateAttribute(string $attribute): void
    {
        $model = $this->model;

        if (!$model->hasAttribute($attribute) || $model->error()->hasError($attribute)) {
            return;
        }

        $value = $model->getAttribute($attribute);

        if ($this->null === true && static::isEmpty($value)) {
            return;
        }

        $methods = $this->getPublicMethods();

        foreach ($methods as $method) {
            $result = call_user_func([$this, $method], $attribute);

            if (!$result) {
                $model->error()->setError($attribute, $this->getMessage());
                return;
            }
        }
    }

    /**
     * Вернуть названия публичных нестатических методов.
     * @return array
     */
    protected function getPublicMethods(): array
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

    /**
     * Указать текст сообщения об ошибке.
     * Если уже указано, то игнорируется.
     * @param string $message
     * @return void
     */
    protected function setMessage(string $message): void
    {
        $this->message = $this->message ?: $message;
    }

    /**
     * Вернуть текст сообщения об ошибке.
     * Если не указан, то вернется сообщение по-умолчанию.
     * @return string
     */
    protected function getMessage(): string
    {
        return $this->message ?: static::DEFAULT_MESSAGE;
    }

    /**
     * Является ли значение пустым.
     * @param mixed $value
     * @return bool
     */
    protected static function isEmpty(mixed $value): bool
    {
        return in_array($value, static::EMPTY_VALUES, true);
    }
}
