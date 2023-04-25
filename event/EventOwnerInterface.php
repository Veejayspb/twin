<?php

namespace twin\event;

/**
 * Имплементируем любой объект от данного интерфейса, чтобы иметь возможность использовать события.
 *
 * Interface EventOwnerInterface
 */
interface EventOwnerInterface
{
    /**
     * Вернуть объект для управления событиями.
     * @return Event
     */
    public function event(): Event;
}
