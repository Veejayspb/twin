<?php

use test\helper\BaseTestCase;
use twin\session\Flash;
use twin\session\Session;

final class FlashTest extends BaseTestCase
{
    const KEY = 'key';
    const MESSAGE = 'message';
    const DATA = [self::KEY => self::MESSAGE];

    public function testConstruct()
    {
        $session = $this->getSession();
        $session->data = [Flash::STORAGE_NAME => self::DATA];
        $flash = $this->getFlash($session);

        $this->assertSame($session, $flash->session);
        $this->assertSame(self::DATA, $flash->messages);
    }

    public function testDestruct()
    {
        $session = $this->getSession();
        $flash = $this->getFlash($session);
        $flash->messages = self::DATA;
        unset($flash);

        $this->assertSame([Flash::STORAGE_NAME => self::DATA], $session->data);
    }

    public function testHas()
    {
        $session = $this->getSession();
        $flash = $this->getFlash($session);

        $actual = $flash->has(self::KEY);
        $this->assertFalse($actual);

        $flash->messages = self::DATA;
        $actual = $flash->has(self::KEY);
        $this->assertTrue($actual);
    }

    public function testGet()
    {
        $session = $this->getSession();
        $flash = $this->getFlash($session);

        // Сообщение еще не задано
        $actual = $flash->get(self::KEY, false);
        $this->assertNull($actual);

        // Получение сообщения без его удаления
        $flash->messages = self::DATA;
        $actual = $flash->get(self::KEY, false);
        $this->assertSame(self::MESSAGE, $actual);

        // Получение сообщения с его удалением
        $actual = $flash->get(self::KEY, true);
        $this->assertSame(self::MESSAGE, $actual);

        // Сообщение уже удалено
        $actual = $flash->get(self::KEY, false);
        $this->assertNull($actual);
    }

    public function testSet()
    {
        $session = $this->getSession();
        $flash = $this->getFlash($session);

        $flash->set(self::KEY, self::MESSAGE);
        $this->assertSame(self::DATA, $flash->messages);
    }

    public function testDelete()
    {
        $session = $this->getSession();
        $flash = $this->getFlash($session);
        $flash->messages = self::DATA;

        $flash->delete(self::KEY);
        $this->assertSame([], $flash->messages);
    }

    /**
     * @param Session $session
     * @return Flash
     */
    protected function getFlash(Session $session)
    {
        return new class($session) extends Flash
        {
            public Session $session;
            public array $messages = [];
        };
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return new class extends Session
        {
            public array $data = [];

            public function __construct() {}

            public function set(string $name, mixed $value): void
            {
                $this->data[$name] = $value;
            }

            public function get(string $name, mixed $default = null): mixed
            {
                return $this->data[$name] ?? $default;
            }

            public function delete(string $name): void
            {
                if (array_key_exists($name, $this->data)) {
                    unset($this->data[$name]);
                }
            }
        };
    }
}
