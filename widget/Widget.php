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
        ob_start();
    }

    public function __toString()
    {
        return $this->run();
    }

    /**
     * Запустить виджет.
     * @return string
     */
    public function run(): string
    {
        return ob_get_clean();
    }
}
