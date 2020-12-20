<?php

namespace twin\widget;

use twin\common\SetPropertiesTrait;
use twin\view\RenderTrait;

abstract class Widget
{
    use SetPropertiesTrait;
    use RenderTrait;

    /**
     * @param array $properties - свойства виджета
     */
    public function __construct(array $properties = [])
    {
        $this->setProperties($properties);
    }

    public function __toString()
    {
        return $this->run();
    }

    /**
     * Запустить виджет.
     * @return string
     */
    abstract public function run(): string;
}
