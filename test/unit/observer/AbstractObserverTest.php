<?php

use test\helper\BaseTestCase;
use twin\observer\AbstractObserver;

final class AbstractObserverTest extends BaseTestCase
{
    public function testConstruct()
    {
        $properties = [
            'a' => true,
            'c' => true,
        ];

        $observer = $this->getObserver($properties);

        $this->assertTrue($observer->a);
        $this->assertFalse($observer->b);
    }

    public function testIsAvailable()
    {
        $observer = $this->getObserver();
        $observer->events = ['test'];

        $available = $observer->isAvailable('test');
        $this->assertTrue($available);

        $available = $observer->isAvailable('undefined');
        $this->assertFalse($available);
    }

    /**
     * @param array $properties
     * @return AbstractObserver
     */
    protected function getObserver(array $properties = []): AbstractObserver
    {
        return new class($properties) extends AbstractObserver
        {
            public bool $a = false;
            public bool $b = false;

            public function update(SplSubject $subject): void
            {

            }
        };
    }
}
