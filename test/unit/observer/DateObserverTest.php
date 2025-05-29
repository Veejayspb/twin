<?php

use test\helper\BaseTestCase;
use twin\event\EventManager;
use twin\observer\DateObserver;
use twin\Twin;

final class DateObserverTest extends BaseTestCase
{
    public function testUpdate()
    {
        $owner = new stdClass;
        $event = EventManager::instance($owner);

        $observer = new DateObserver;
        $observer->property = 'test';

        $observer->format = 'Y-m-d H:i:s.u';
        $observer->update($event);
        $this->assertSame(Twin::date()->format($observer->format), $owner->test);
    }
}
