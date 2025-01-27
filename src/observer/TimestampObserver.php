<?php

namespace twin\observer;

use twin\event\Event;
use twin\model\Model;

class TimestampObserver extends AbstractObserver
{
    public $events = [
        Event::BEFORE_INSERT,
        Event::BEFORE_UPDATE,
    ];

    /**
     * Атрибут, который заполняется при сохранении модели.
     * @var string
     */
    public $created = 'date_create';

    /**
     * Атрибут, который заполняется при изменении модели.
     * @var string
     */
    public $updated = 'date_update';

    /**
     * {@inheritdoc}
     */
    public function update(Event $event, string $name): void
    {
        $owner = $event->getOwner();
        $timestamp = time();

        if (!is_a($owner, Model::class)) {
            return;
        }

        if ($name == Event::BEFORE_INSERT) {
            $owner->setAttribute($this->created, $timestamp);
        }

        $owner->setAttribute($this->updated, $timestamp);
    }
}
