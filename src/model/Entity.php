<?php

namespace twin\model;

use ReflectionClass;
use ReflectionProperty;

abstract class Entity
{
    public function __construct()
    {

    }

    /**
     * Атрибуты сущности.
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
     * Существует ли атрибут.
     * @param string $name - название атрибута
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        $names = $this->attributeNames();
        return in_array($name, $names);
    }
}
