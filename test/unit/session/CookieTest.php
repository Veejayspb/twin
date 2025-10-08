<?php

use test\helper\BaseTestCase;
use twin\session\Cookie;

final class CookieTest extends BaseTestCase
{
    public static bool $setCookie = true;

    public function testSet()
    {
        $cookie = $this->getCookie();
        $expected = [$cookie->prefix . 'name-1' => 'value'];

        // Успешное добавление куки
        self::$setCookie = true;
        $result = $cookie->set('name-1', 'value');
        $this->assertTrue($result);
        $this->assertSame($expected, $_COOKIE);

        // Неудачное добавление куки
        self::$setCookie = false;
        $result = $cookie->set('name-2', 'value');
        $this->assertFalse($result);
        $this->assertSame($expected, $_COOKIE);
    }

    public function testGet()
    {
        $cookie = $this->getCookie();
        $default = 'default';

        $actual = $cookie->get('undefined');
        $this->assertNull($actual);

        $actual = $cookie->get('undefined', $default);
        $this->assertSame($default, $actual);

        $_COOKIE[$cookie->prefix . 'name'] = 'value';

        $actual = $cookie->get('name');
        $this->assertSame('value', $actual);

        $actual = $cookie->get('name', $default);
        $this->assertSame('value', $actual);
    }

    public function testDelete()
    {
        $cookie = $this->getCookie();
        $expected = $_COOKIE = [$cookie->prefix . 'name' => 'value'];

        // Неудачное удаление куки
        self::$setCookie = false;
        $result = $cookie->delete('name');
        $this->assertFalse($result);
        $this->assertSame($expected, $_COOKIE);

        // Успешное удаление куки
        self::$setCookie = true;
        $result = $cookie->delete('name');
        $this->assertTrue($result);
        $this->assertSame([], $_COOKIE);
    }

    /**
     * @return Cookie
     */
    protected function getCookie()
    {
        return new class extends Cookie
        {
            protected function setCookie(string $name, string $value, int $expire = 0): bool
            {
                return CookieTest::$setCookie;
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_COOKIE = [];
    }
}
