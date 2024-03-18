<?php

namespace twin\event;

use twin\behavior\Behavior;

/**
 * Класс для управления событиями.
 *
 * $event = new Event($owner)
 * $event->attach($behavior)
 * $event->notify('event-name')
 *
 * Class Event
 */
class Event
{
    /**
     * Родительский объект, который использует события.
     * @var EventOwnerInterface
     */
    protected $owner;

    /**
     * Список подписчиков.
     * @var Behavior[]
     */
    protected $behaviors = [];

    /**
     * @param EventOwnerInterface $owner
     */
    public function __construct(EventOwnerInterface $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Подписка на событие.
     * @param Behavior $behavior - поведение
     * @return void
     */
    public function attach(Behavior $behavior)
    {
        $this->behaviors[] = $behavior;
    }

    /**
     * Отписка от события.
     * @param Behavior $behavior - поведение
     * @return void
     */
    public function detach(Behavior $behavior)
    {
        foreach ($this->behaviors as $key => $value) {
            if ($value !== $behavior) continue;

            unset($this->behaviors[$key]);
            break;
        }
    }

    /**
     * Оповещение о событии.
     * @param string $event - название события
     * @return void
     */
    public function notify(string $event)
    {
        foreach ($this->behaviors as $behavior) {
            $behavior->touch($event);
        }
    }
}
