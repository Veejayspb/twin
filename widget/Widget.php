<?php

namespace twin\widget;

use twin\helper\ObjectHelper;
use twin\view\RenderTrait;

abstract class Widget
{
    use RenderTrait;

    /**
     * @param array $properties - свойства виджета
     */
    public function __construct(array $properties = [])
    {
        ObjectHelper::fill($this, $properties);
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
