<?php

namespace twin\behavior;

interface BehaviorOwnerInterface
{
    /**
     * Вернуть ранее зарегистрированное поведение.
     * @param string $name - название
     * @return Behavior|null
     */
    public function getBehavior(string $name);

    /**
     * Зарегистрировать поведение.
     * @param string $name - название
     * @param Behavior $behavior - объект с поведением
     * @return void
     */
    public function setBehavior(string $name, Behavior $behavior);
}
