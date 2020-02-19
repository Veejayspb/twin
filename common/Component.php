<?php

namespace twin\common;

abstract class Component
{
    /**
     * @param array $properties - свойства объекта
     */
    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }

    /**
     * Установить свойства объекта.
     * @param array $properties - свойства
     * @return void
     */
    protected function setProperties(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (!property_exists($this, $name)) continue;
            $this->$name = $value;
        }
    }
}
