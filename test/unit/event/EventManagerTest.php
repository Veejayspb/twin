<?php

use test\helper\BaseTestCase;
use twin\event\EventManager;
use twin\observer\AbstractObserver;

final class EventManagerTest extends BaseTestCase
{
    public static array $observersUpdated = [];

    public function testGetOwner()
    {
        $owner = new stdClass;
        $event = EventManager::instance($owner);

        $this->assertSame($owner, $event->getOwner());
    }

    public function testGetObservers()
    {
        $event = EventManager::instance(new stdClass);
        $observer1 = $this->createObserver();
        $observer2 = $this->createObserver();

        $property = new ReflectionProperty(EventManager::class, 'observers');
        $property->setValue($event, [
            spl_object_id($observer1) => $observer1,
            spl_object_id($observer2) => $observer2,
        ]);

        $this->assertSame(
            [$observer1, $observer2],
            $event->getObservers()
        );
    }

    public function testAttach()
    {
        $event = EventManager::instance(new stdClass);
        $observer1 = $this->createObserver();
        $observer2 = $this->createObserver();

        $event->attach($observer1);
        $this->assertSame(
            [$observer1],
            $event->getObservers()
        );

        $event->attach($observer2);
        $this->assertSame(
            [$observer1, $observer2],
            $event->getObservers()
        );

        $event->attach($observer1);
        $this->assertSame(
            [$observer1, $observer2],
            $event->getObservers()
        );
    }

    public function testDetach()
    {
        $event = EventManager::instance(new stdClass);
        $observer1 = $this->createObserver();
        $observer2 = $this->createObserver();

        $event->attach($observer2);
        $event->attach($observer1);

        $this->assertSame(
            [$observer2, $observer1],
            $event->getObservers()
        );

        $event->detach($observer2);
        $this->assertSame(
            [$observer1],
            $event->getObservers()
        );

        $event->detach($observer1);
        $this->assertSame(
            [],
            $event->getObservers()
        );
    }

    public function testNotify()
    {
        $event = EventManager::instance(new stdClass);
        $observer1 = $this->createObserver();
        $observer2 = $this->createObserver();
        $event->attach($observer1);
        $event->attach($observer2);
        $event->notify('test');

        $this->assertSame(
            [$observer1, $observer2],
            self::$observersUpdated
        );
    }

    public function testInstance()
    {
        $owner1 = new stdClass;
        $owner2 = new stdClass;

        $this->assertSame(EventManager::instance($owner1), EventManager::instance($owner1));
        $this->assertNotSame(EventManager::instance($owner1), EventManager::instance($owner2));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $property = new ReflectionProperty(EventManager::class, 'instances');
        $property->setValue([]);
    }

    /**
     * Инстанцировать и вернуть нового наблюдателя.
     * @return AbstractObserver
     */
    protected function createObserver(): AbstractObserver
    {
        return new class extends AbstractObserver
        {
            public function update(SplSubject $subject): void
            {
                EventManagerTest::$observersUpdated[] = $this;
            }

            public function isAvailable(string $event): bool
            {
                return true;
            }
        };
    }
}
