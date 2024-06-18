<?php

namespace twin\event;

class Event
{
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
     * @param string $name
     * @param AbstractObserver $observer
     * @return void
     */
    public function attachObserver(string $name, AbstractObserver $observer): void
    {
        $this->observers[$name] = $observer;
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
