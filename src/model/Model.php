<?php

namespace twin\model;

use ReflectionClass;
use ReflectionProperty;

abstract class Model
{
    /**
     * Объект с ошибками.
     * @return Error
     */
    public function error(): Error
    {
        return Error::instance($this);
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
     * Атрибуты модели.
     * @return array
     */
    public function attributeNames(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $attributes = [];

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $attributes[] = $property->name;
        }

        return $attributes;
    }

    /**
     * Вернуть значение атрибута.
     * @param string $name
     * @return mixed
     */
    public function getAttribute(string $name): mixed
    {
        if ($this->hasAttribute($name)) {
            return $this->$name;
        } else {
            return null;
        }
    }

    /**
     * Присвоить значение атрибута с проверкой на существование.
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $name, mixed $value): void
    {
        if ($this->hasAttribute($name)) {
            $this->$name = $value;
        }
    }

    /**
     * Присвоить значения атрибутов.
     * @param array $attributes - значения атрибутов
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
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
     * Валидация.
     * @param array $attributes - названия атрибутов для валидации (если не указать, то провалидируются все)
     * @return bool
     */
    public function validate(array $attributes = []): bool
    {
        $this->rules();

        // Сбросить ошибки атрибутов, для которых не требуется валидация
        if ($attributes) {
            $clearAttributes = array_diff(
                array_keys($this->getAttributes()),
                $attributes
            );

            $this->error()->clearErrors($clearAttributes);
        }

        return !$this->error()->hasErrors();
    }

    /**
     * Вызов набора пользовательских валидаторов.
     * @return void
     */
    protected function rules(): void {}
}
