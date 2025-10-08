<?php

use test\helper\BaseTestCase;
use twin\session\Session;

final class SessionTest extends BaseTestCase
{
    public function testSet()
    {
        $session = $this->getSession();
        $expected = [];
        $values = [
            'value',
            ['value'],
            1,
        ];

        foreach ($values as $value) {
            $key = 'key' . rand(1, 3);
            $session->set($key, $value);
            $expected[$session->prefix . $key] = $value;
            $this->assertSame($expected, $_SESSION);
        }
    }

    public function testGet()
    {
        $session = $this->getSession();
        $default = 'default';
        $key = 'key';
        $value = 'value';
        $_SESSION = [
            $session->prefix . $key => $value,
        ];

        // Значение не задано
        $actual = $session->get('undefined', $default);
        $this->assertSame($default, $actual);

        // Значение задано
        $actual = $session->get($key);
        $this->assertSame($value, $actual);
    }

    public function testDelete()
    {
        $session = $this->getSession();
        $key = 'key';
        $value = 'value';
        $_SESSION = [
            $session->prefix . $key => $value,
        ];

        $session->delete('key');
        $this->assertSame([], $_SESSION);
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return new class extends Session
        {
            public function __construct() {}
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }
}
