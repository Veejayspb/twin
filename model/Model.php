<?php

namespace twin\model;

use ReflectionClass;
use ReflectionProperty;
use twin\common\Exception;

abstract class Model
{
    /**
     * Ошибки валидации.
     * @var array
     */
    protected $_errors = [];

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->isServiceAttribute($name)) {
            throw new Exception(500, "Undefined attribute $name");
        }
        return $this->$name;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->isServiceAttribute($name)) {
            throw new Exception(500, "Attribute $name doesn't exists");
        }
        $this->$name = $value;
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
    public function setError(string $attribute, string $message)
    {
        $this->_errors[$attribute] = $message;
    }

    /**
     * Добавить ошибки валидации атрибутам.
     * @param array $errors
     * ключ - название атрибута
     * значение - текст ошибки
     * @return void
     */
    public function setErrors(array $errors)
    {
        foreach ($errors as $attribute => $message) {
            if (!$this->hasAttribute($attribute)) continue;
            $this->setError($attribute, $message);
        }
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
            $message = $this->getError($attribute);
            if ($message === null) continue;
            $result[$attribute] = $message;
        }
        return $result;
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
     * Очистить ошибки валидации атрибута.
     * @param string $attribute - название атрибута
     * @return void
     */
    public function clearError(string $attribute)
    {
        if (array_key_exists($attribute, $this->_errors)) {
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Очистить все ошибки валидации.
     * @return void
     */
    public function clearErrors()
    {
        $this->_errors = [];
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
        $names = $safeOnly ? $this->safe() : $this->attributeNames();
        foreach ($attributes as $name => $value) {
            if (!in_array($name, $names)) continue;
            $this->$name = $value;
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
     * Присвоить значения атрибутов и провалидировать.
     * @param array $attributes - значения атрибутов
     * @return bool - результат валидации
     * @see validate()
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
        if (!$this->beforeValidate()) return false;
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
     * @return bool
     */
    protected function beforeValidate(): bool
    {
        return true;
    }

    /**
     * Вызов события после валидации.
     * @return void
     */
    protected function afterValidate() {}

    /**
     * Является ли атрибут сервисным.
     * @param string $name - название атрибута
     * @return bool
     */
    private function isServiceAttribute(string $name): bool
    {
        return substr($name, 0, 1) == '_';
    }
}
