<?php

namespace twin\observer;

use twin\event\Event;
use twin\helper\StringHelper;
use twin\model\Model;

class SlugObserver extends AbstractObserver
{
    public $events = [
        Event::BEFORE_VALIDATE,
    ];

    /**
     * Название атрибута из которого берем текст.
     * @var string
     */
    public $from;

    /**
     * Название атрибута, куда кладём транслитерацию.
     * @var string
     */
    public $to;

    /**
     * Заполнить поле для транслита даже если оно непустое.
     * @var bool
     */
    public $force = false;

    /**
     * {@inheritdoc}
     */
    public function update(Event $event, string $name): void
    {
        $owner = $event->getOwner();

        if (!is_a($owner, Model::class)) {
            return;
        }

        if ($this->force === false && $owner->{$this->to} !== null) {
            return;
        }

        $value = $owner->{$this->from};

        if (!is_string($value)) {
            return;
        }

        $slug = StringHelper::slug($value);
        $owner->setAttribute($this->to, $slug);
    }
}
