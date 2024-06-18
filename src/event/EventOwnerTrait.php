<?php

namespace twin\event;

trait EventOwnerTrait
{
    /**
     * Вернуть объект для управления событиями.
     * @return Event
     */
    public function event(): Event
    {
        return Event::instance($this);
    }
}
