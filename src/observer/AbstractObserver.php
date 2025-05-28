<?php

namespace twin\observer;

use SplObserver;
use SplSubject;
use twin\event\EventManager;
use twin\helper\ObjectHelper;

abstract class AbstractObserver implements SplObserver
{
    /**
     * Названия событий, при которых срабатывает наблюдатель.
     * @var array
     */
    public array $events = [];

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $objectHelper = new ObjectHelper($this);
        $objectHelper->setProperties($properties);
    }

    /**
     * Будет ли наблюдатель запущен при указанном событии.
     * @param string $event
     * @return bool
     */
    public function isAvailable(string $event): bool
    {
        return in_array($event, $this->events);
    }

    /**
     * {@inheritdoc}
     * @param EventManager $subject
     */
    abstract public function update(SplSubject $subject): void;
}
