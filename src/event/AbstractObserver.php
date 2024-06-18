<?php

namespace twin\event;

use twin\helper\ObjectHelper;

abstract class AbstractObserver
{
    /**
     * Названия событий, при которых срабатывает наблюдатель.
     * @var array
     */
    public $events = [];

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $objectHelper = new ObjectHelper($this);
        $objectHelper->setProperties($properties);
    }

    /**
     * Действия наблюдателя при срабатывании события.
     * @param Event $event
     * @param string $name
     * @return void
     */
    abstract public function update(Event $event, string $name): void;
}
