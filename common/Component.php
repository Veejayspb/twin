<?php

namespace twin\common;

abstract class Component
{
    use SetPropertiesTrait;

    /**
     * @param array $properties - свойства объекта
     */
    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }
}
