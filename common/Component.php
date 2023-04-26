<?php

namespace twin\common;

use twin\helper\ObjectHelper;

abstract class Component
{
    /**
     * @param array $properties - свойства объекта
     */
    public function __construct(array $properties = [])
    {
        ObjectHelper::setProperties($this, $properties);
    }
}
