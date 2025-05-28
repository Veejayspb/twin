<?php

namespace twin\event;

use SplObserver;
use SplSubject;
use twin\observer\AbstractObserver;

/**
 * Контейнер для хранения и работы с событиями.
 *
 * $event = EventManager::instance($model);
 * $event->attach(new AbstractObserver);
 * $event->notify('event-name');
 *
 * Class Event
 */
class EventManager implements SplSubject
{
    const AFTER_INIT = 'after-init';
    const BEFORE_VALIDATE = 'before-validate';
    const AFTER_VALIDATE = 'after-validate';
    const BEFORE_INSERT = 'before-insert';
    const AFTER_INSERT = 'after-insert';
    const BEFORE_UPDATE = 'before-update';
    const AFTER_UPDATE = 'after-update';

    /**
     * Объект, к которому привязан менеджер событий.
     * @var object
     */
    protected object $owner;

    /**
     * Список прикрепленных наблюдателей.
     * @var AbstractObserver[]
     */
    protected array $observers = [];

    /**
     * Список инстансов.
     * @var static[]
     */
    protected static array $instances = [];

    /**
     * @param object $owner
     */
    protected function __construct(object $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Вернуть объект, к которому привязан менеджер событий.
     * @return object
     */
    public function getOwner(): object
    {
        return $this->owner;
    }

    /**
     * Вернуть список прикрепленных наблюдателей.
     * @return AbstractObserver[]
     */
    public function getObservers(): array
    {
        return array_values($this->observers);
    }

    /**
     * {@inheritdoc}
     * @param AbstractObserver $observer
     */
    public function attach(SplObserver $observer): void
    {
        $objectId = spl_object_id($observer);
        $this->observers[$objectId] = $observer;
    }

    /**
     * {@inheritdoc}
     * @param AbstractObserver $observer
     */
    public function detach(SplObserver $observer): void
    {
        $objectId = spl_object_id($observer);

        if (array_key_exists($objectId, $this->observers)) {
            unset($this->observers[$objectId]);
        }
    }

    /**
     * {@inheritdoc}
     * @param string $event
     */
    public function notify(string $event = null): void
    {
        $observers = $this->getObservers();

        foreach ($observers as $observer) {
            $isAvailable = $observer->isAvailable($event);

            if ($isAvailable) {
                $observer->update($this);
            }
        }
    }

    /**
     * Инстанцировать объект события.
     * @param object $owner
     * @return static
     */
    public static function instance(object $owner): static
    {
        $objectId = spl_object_id($owner);

        if (!array_key_exists($objectId, static::$instances)) {
            static::$instances[$objectId] = new static($owner);
        }

        return static::$instances[$objectId];
    }
}
