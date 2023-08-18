<?php

namespace twin\common;

use twin\helper\ObjectHelper;

abstract class Component
{
    /**
     * Список обязательных для заполнения свойств.
     * @var array
     */
    protected $_requiredProperties = [];

    /**
     * @param array $properties - свойства объекта
     * @throws Exception
     */
    public function __construct(array $properties = [])
    {
        ObjectHelper::setProperties($this, $properties);
        $properties = $this->getRequiredEmptyProperties();

        if ($properties) {
            throw new Exception(500, static::class . ' - required properties not specified: ' . implode(', ', $properties));
        }
    }

    /**
     * Вернуть незаполненные обязательные свойства.
     * @return array
     */
    protected function getRequiredEmptyProperties(): array
    {
        $result = [];

        foreach ($this->_requiredProperties as $name) {
            if (!property_exists($this, $name)) {
                continue;
            }

            if ($this->isEmpty($this->$name)) {
                $result[] = $name;
            }
        }

        return $result;
    }

    /**
     * Является ли значение пустым.
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        $emptyValues = [
            null,
            [],
        ];

        return in_array($value, $emptyValues, true);
    }
}
