<?php

use twin\helper\Alias;
use test\helper\BaseTestCase;

final class AliasTest extends BaseTestCase
{
    public function testSet()
    {
        $result = Alias::set('@a', 'a');
        $this->assertTrue($result);
        $this->assertSame('a', Alias::get('@a'));

        $result = Alias::set('@a', '');
        $this->assertTrue($result);
        $this->assertSame('', Alias::get('@a'));

        $result = Alias::set('@not-allowed', 'na');
        $this->assertFalse($result);
        $this->assertSame('@not-allowed', Alias::get('@not-allowed'));

        $result = Alias::set('@a', '@a/test/path');
        $this->assertFalse($result);
        $this->assertSame('', Alias::get('@a'));
    }

    public function testIsset()
    {
        $this->assertFalse(Alias::isset('@a'));

        Alias::set('@a', 'a');
        $this->assertTrue(Alias::isset('@a'));

        Alias::set('@b', '');
        $this->assertTrue(Alias::isset('@b'));

        Alias::set('@not-allowed', 'na');
        $this->assertFalse(Alias::isset('@not-allowed'));

        Alias::set('@c', '@c/test/path');
        $this->assertFalse(Alias::isset('@c'));
    }

    public function testUnset()
    {
        Alias::set('@a', 'a');
        Alias::set('@b', 'b');

        $this->assertTrue(Alias::isset('@a'));
        $this->assertTrue(Alias::isset('@b'));

        Alias::unset('@a');
        $this->assertFalse(Alias::isset('@a'));
        $this->assertTrue(Alias::isset('@b'));

        Alias::unset('@b');
        $this->assertFalse(Alias::isset('@a'));
        $this->assertFalse(Alias::isset('@b'));
    }

    public function testGet()
    {
        Alias::set('@a', 'a');
        Alias::set('@b', 'b');
        Alias::set('@c', '@b/c');

        $actual = Alias::get('@a');
        $this->assertSame('a', $actual);

        $actual = Alias::get('@b/y');
        $this->assertSame('b/y', $actual);

        $actual = Alias::get('@c');
        $this->assertSame('b/c', $actual);

        $actual = Alias::get('@notexists');
        $this->assertSame('@notexists', $actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new ReflectionClass(new Alias);
        $property = $reflection->getProperty('aliases');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}
