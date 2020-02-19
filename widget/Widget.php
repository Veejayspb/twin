<?php

namespace twin\widget;

use twin\view\RenderTrait;

abstract class Widget
{
    use RenderTrait;

    /**
     * @param array $properties - свойства виджета
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
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
