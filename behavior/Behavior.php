<?php

namespace twin\behavior;

use twin\Twin;

abstract class Behavior
{
    /**
     * Объект, к которому привязано поведение.
     * @var BehaviorOwnerInterface
     */
    protected $owner;

    /**
     * @param BehaviorOwnerInterface $owner
     * @param array $properties
     */
    public function __construct(BehaviorOwnerInterface $owner, array $properties = [])
    {
        $this->owner = $owner;
        Twin::createObject($this, $properties);
    }

    /**
     * Выполнение сценария при срабатывании события.
     * @param string $event - название события
     * @return void
     */
    public function touch(string $event) {}
}
