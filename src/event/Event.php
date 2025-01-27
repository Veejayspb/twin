<?php

namespace twin\event;

use twin\observer\AbstractObserver;

class Event
{
    const AFTER_INIT = 'after-init';
    const BEFORE_VALIDATE = 'before-validate';
    const AFTER_VALIDATE = 'after-validate';
    const BEFORE_INSERT = 'before-insert';
    const AFTER_INSERT = 'after-insert';
    const BEFORE_UPDATE = 'before-update';
    const AFTER_UPDATE = 'after-update';

    /**
     * Объект, к которому привязан объект-событие.
     * @var object
     */
    protected $owner;

    /**
     * Список прикрепленных наблюдателей.
     * @var AbstractObserver[]
     */
    protected $observers = [];

    /**
     * Список инстансов.
     * @var static[]
     */
    protected static $instances = [];

    /**
     * @param object $owner
     */
    protected function __construct(object $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Вернуть объект, к которому привязан объект-событие.
     * @return object
     */
    public function getOwner(): object
    {
        return $this->owner;
    }

    /**
     * Прикрепить наблюдателя.
     * @param AbstractObserver $observer
     * @param string|null $name
     * @return void
     */
    public function attachObserver(AbstractObserver $observer, ?string $name = null): void
    {
        if ($name === null) {
            $this->observers[] = $observer;
        } else {
            $this->observers[$name] = $observer;
        }
    }

    /**
     * Открепить наблюдателя.
     * @param string $name
     * @return void
     */
    public function detachObserver(string $name): void
    {
        if (array_key_exists($name, $this->observers)) {
            unset($this->observers[$name]);
        }
    }

    /**
     * Вернуть список прикрепленных наблюдателей.
     * @return AbstractObserver[]
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * Вернуть наблюдателя по его названию.
     * @param string $name
     * @return AbstractObserver|null
     */
    public function getObserver(string $name): ?AbstractObserver
    {
        return $this->observers[$name] ?? null;
    }

    /**
     * Отправить уведомления наблюдателям.
     * @param string $name - название события
     * @return void
     */
    public function notify(string $name): void
    {
        foreach ($this->observers as $observer) {
            if (in_array($name, $observer->events)) {
                $observer->update($this, $name);
            }
        }
    }

    /**
     * Вернуть событие для указанного объекта.
     * @param object $owner
     * @return static
     */
    public static function instance(object $owner): self
    {
        foreach (static::$instances as $instance) {
            if ($instance->owner === $owner) {
                return $instance;
            }
        }

        return static::$instances[] = new static($owner);
    }
}
