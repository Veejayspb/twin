<?php

namespace twin\model;

use ReflectionClass;
use twin\event\Event;
use twin\event\EventOwnerTrait;
use twin\helper\StringHelper;

abstract class Model
{
    use EventOwnerTrait;

    /**
     * Значения атрибутов.
     * @var array
     */
    protected $_attributes = [];

    /**
     * Ошибки валидации.
     * @var array
     */
    protected $_errors = [];

    public function __construct()
    {
        $this->event()->notify(Event::AFTER_INIT);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Ярлыки атрибутов.
     * @return array
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Ярлык атрибута.
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getLabel(string $attribute): string
    {
        $labels = $this->labels();
        return $labels[$attribute] ?? $attribute;
    }

    /**
     * Добавить ошибку валидации атрибута.
     * @param string $attribute - название атрибута
     * @param string $message - текст ошибки
     * @return void
     */
    public function setError(string $attribute, string $message): void
    {
        if ($this->hasAttribute($attribute)) {
            $this->_errors[$attribute] = $message;
        }
    }

    /**
     * Добавить ошибки валидации атрибутам.
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors): void
    {
        foreach ($errors as $attribute => $message) {
            $this->setError($attribute, $message);
        }
    }

    /**
     * Вернуть последнюю ошибку валидации атрибута.
     * @param string $attribute - название атрибута
     * @return string|null - NULL, если ошибки отсутствуют
     */
    public function getError(string $attribute): ?string
    {
        return $this->_errors[$attribute] ?? null;
    }

    /**
     * Вернуть массив ошибок валидации.
     * @param array $attributes - если не указано, то вернет все ошибки
     * @return array
     */
    public function getErrors(array $attributes = []): array
    {
        if (empty($attributes)) {
            return $this->_errors;
        }

        $result = [];

        foreach ($attributes as $attribute) {
            if (!is_string($attribute)) {
                continue;
            }

            $message = $this->getError($attribute);

            if ($message === null) {
                continue;
            }

            $result[$attribute] = $message;
        }

        return $result;
    }

    /**
     * Имеется ли ошибка валидации у указанного атрибута.
     * @param string $attribute - название атрибута
     * @return bool
     */
    public function hasError(string $attribute): bool
    {
        return null !== $this->getError($attribute);
    }

    /**
     * Имеются ли ошибки валидации.
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->_errors);
    }

    /**
     * Сбросить ошибки валидации атрибута.
     * @param string $attribute - название атрибута
     * @return void
     */
    public function clearError(string $attribute): void
    {
        if (array_key_exists($attribute, $this->_errors)) {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Сбросить ошибки валидации указанных атрибутов.
     * @param array $attributes - названия атрибутов, для которых требуется сбросить ошибки (если не указано, то сбросятся все)
     * @return void
     */
    public function clearErrors(array $attributes = []): void
    {
        $attributes = $attributes ?: array_keys($this->_errors);

        foreach ($attributes as $attribute) {
            $this->clearError($attribute);
        }
    }

    /**
     * Вернуть значение атрибута.
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name)
    {
        return $this->_attributes[$name] ?? null;
    }

    /**
     * Присвоить значение атрибута с проверкой на существование.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, $value): void
    {
        if ($this->hasAttribute($name)) {
            $this->_attributes[$name] = $value;
        }
    }

    /**
     * Присвоить значения атрибутов.
     * @param array $attributes - значения атрибутов
     * @param bool $safeOnly - только безопасные
     * @return static
     */
    public function setAttributes(array $attributes, bool $safeOnly = true): self
    {
        $names = $safeOnly ? $this->safe() : $this->attributeNames();

        foreach ($attributes as $name => $value) {
            if (!in_array($name, $names)) {
                continue;
            }

            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Вернуть значения атрибутов.
     * @param array $attributes - названия атрибутов (если указано, то вернет только указанные атрибуты)
     * @return array
     */
    public function getAttributes(array $attributes = []): array
    {
        $names = $this->attributeNames();
        $skip = !empty($attributes);
        $result = [];

        foreach ($names as $name) {
            if ($skip && !in_array($name, $attributes)) {
                continue;
            }

            $result[$name] = $this->getAttribute($name);
        }

        return $result;
    }

    /**
     * Существует ли атрибут.
     * @param string $name - название атрибута
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        $names = $this->attributeNames();
        return in_array($name, $names);
    }

    /**
     * Названия безопасных атрибутов.
     * @return array
     */
    public function safe(): array
    {
        return $this->attributeNames();
    }

    /**
     * Является ли атрибут безопасным.
     * @param string $name - название атрибута
     * @return bool
     */
    public function isSafeAttribute(string $name): bool
    {
        $safeAttributes = $this->safe();
        return in_array($name, $safeAttributes);
    }

    /**
     * Валидация.
     * @param array $attributes - названия атрибутов для валидации (если не указать, то провалидируются все)
     * @return bool
     */
    public function validate(array $attributes = []): bool
    {
        $event = $this->event();
        $event->notify(Event::BEFORE_VALIDATE);
        $this->rules();

        // Сбросить ошибки атрибутов, для которых не требуется валидация
        if ($attributes) {
            $clearAttributes = array_diff(
                array_keys($this->getAttributes()),
                $attributes
            );

            $this->clearErrors($clearAttributes);
        }

        $event->notify(Event::AFTER_VALIDATE);

        return !$this->hasErrors();
    }

    /**
     * Название таблицы в БД.
     * @return string
     */
    public static function tableName(): string
    {
        $reflection = new ReflectionClass(static::class);

        if ($reflection->isAnonymous()) {
            return '';
        }

        $className = $reflection->getShortName();
        $name = StringHelper::camelToKabob($className);
        return str_replace('-', '_', $name);
    }

    /**
     * Преобразовать данные в массив моделей.
     * @param array $data
     * @return static[]
     */
    public static function propagate(array $data): array
    {
        $models = [];

        foreach ($data as $row) {
            $model = new static;
            $model->setAttributes($row, false);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Вызов набора пользовательских валидаторов.
     * @return void
     */
    protected function rules(): void {}

    /**
     * Атрибуты модели.
     * @return array
     */
    abstract protected function attributeNames(): array;
}
