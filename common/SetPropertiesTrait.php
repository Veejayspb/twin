<?php

namespace twin\common;

trait SetPropertiesTrait
{
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
