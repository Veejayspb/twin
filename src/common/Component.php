<?php

namespace twin\common;

use twin\helper\ObjectHelper;

abstract class Component
{
    /**
     * Список пустых значений свойств.
     */
    const EMPTY_VALUES = [
        null,
        [],
    ];

    /**
     * Список обязательных для заполнения свойств.
     * @var array
     */
    protected array $_requiredProperties = [];

    /**
     * @param array $properties - свойства объекта
     * @throws Exception
     */
    public function __construct(array $properties = [])
    {
        (new ObjectHelper($this))->setProperties($properties);
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
    protected function isEmpty(mixed $value): bool
    {
        return in_array($value, static::EMPTY_VALUES, true);
    }
}
