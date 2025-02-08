<?php

namespace twin\model;

use twin\event\Event;
use twin\event\EventOwnerTrait;

abstract class Model extends Entity
{
    use EventOwnerTrait;

    public function __construct()
    {
        $this->event()->notify(Event::AFTER_INIT);
    }
}
