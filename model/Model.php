<?php

namespace twin\model;

use ReflectionClass;
use ReflectionProperty;

abstract class Model
{
    /**
     * Ошибки валидации.
     * @var array
     */
    protected $_errors = [];

    /**
     * Ярлыки атрибутов.
     * @return array
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Подсказки для атрибутов.
     * @return array
     */
    public function hints(): array
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
        return array_key_exists($attribute, $labels) ? $labels[$attribute] : $attribute;
    }

    /**
     * Комментарий для атрибута.
     * @param string $attribute - название атрибута
     * @return string|null
     */
    public function getHint(string $attribute)
    {
        $hints = $this->hints();
        return array_key_exists($attribute, $hints) ? $hints[$attribute] : null;
    }

    /**
     * Добавить ошибку валидации атрибута.
     * @param string $attribute - название атрибута
     * @param string $message - текст ошибки
     * @return void
     */
    public function addError(string $attribute, string $message)
    {
        $this->_errors[$attribute] = $message;
    }

    /**
     * Вернуть последнюю ошибку валидации атрибута.
     * @param string $attribute - название атрибута
     * @return string|null - NULL, если ошибки отсутствуют
     */
    public function getError(string $attribute)
    {
        return array_key_exists($attribute, $this->_errors) ? $this->_errors[$attribute] : null;
    }

    /**
     * Вернуть массив ошибок валидации.
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
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
     * Атрибуты модели.
     * @return array
     */
    protected function attributeNames(): array
    {
        $properties = (new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            if ($property->isStatic()) continue;
            $result[] = $property->getName();
        }
        return $result;
    }

    /**
     * Присвоить значения атрибутов.
     * @param array $attributes - значения атрибутов
     * @param bool $safeOnly - только безопасные
     * @return void
     */
    public function setAttributes(array $attributes, bool $safeOnly = true)
    {
        $modelAttributes = $this->getAttributes();
        foreach ($attributes as $name => $value) {
            if (!array_key_exists($name, $modelAttributes)) continue;
            $isSafe = $this->isSafeAttribute($name);
            if (!$safeOnly || $isSafe) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Вернуть значения атрибутов.
     * @param array $attributes - атрибуты и их значения (если указано, то вернет только указанные атрибуты)
     * @return array
     */
    public function getAttributes(array $attributes = []): array
    {
        $names = $this->attributeNames();
        $skip = !empty($attributes);
        $result = [];
        foreach ($names as $name) {
            if ($skip && !in_array($name, $attributes)) continue;
            $result[$name] = $this->$name;
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
        $attributes = $this->attributeNames();
        return in_array($name, $attributes);
    }

    /**
     * Безопасные атрибуты.
     * @return array
     */
    public function getSafeAttributes(): array
    {
        $attributes = $this->getAttributes();
        return array_keys($attributes);
    }

    /**
     * Является ли атрибут безопасным.
     * @param string $name - название атрибута
     * @return bool
     */
    public function isSafeAttribute(string $name): bool
    {
        $safeAttributes = $this->getSafeAttributes();
        return in_array($name, $safeAttributes);
    }

    /**
     * Присвоить значения атрибутов и провалидировать.
     * @param array $attributes - значения атрибутов
     * @return bool
     */
    public function load(array $attributes): bool
    {
        $this->setAttributes($attributes);
        return $this->validate();
    }

    /**
     * Валидация.
     * @return bool
     */
    public function validate(): bool
    {
        $this->beforeValidate();
        $this->rules();
        $result = !$this->hasErrors();
        $this->afterValidate();
        return $result;
    }

    /**
     * Вызов набора пользовательских валидаторов.
     * @return void
     */
    protected function rules() {}

    /**
     * Вызов события до валидации.
     * @return void
     */
    protected function beforeValidate() {}

    /**
     * Вызов события после валидации.
     * @return void
     */
    protected function afterValidate() {}
}
