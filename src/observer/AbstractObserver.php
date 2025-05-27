<?php

namespace twin\observer;

use SplObserver;
use SplSubject;
use twin\event\EventManager;

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
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
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
