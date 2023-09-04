<?php

use PHPUnit\Framework\MockObject\MockObject;
use twin\helper\Header;
use twin\test\helper\BaseTestCase;

final class HeaderTest extends BaseTestCase
{
    const RAW_HEADERS = [
        'Content-type: text/xml;charset=UTF-8',
        'Content-type: text/html;charset=UTF-8',
        'Cache-Control: max-age=604800',
        'invalid:header',
        'invalid header',
        '',
    ];

    public function testAddMultiple()
    {
        $object = new \twin\test\helper\Header;

        $object->addMultiple([
            'zero' => 'old',
            'one' => 'old',
        ]);
        $this->assertSame([
            'zero' => 'old',
            'one' => 'old',
        ], $object->getList());

        $object->addMultiple([
            'one' => 'new',
            'two' => 'new',
            'three' => 123,
        ]);
        $this->assertSame([
            'zero' => 'old',
            'one' => 'new',
            'two' => 'new',
            'three' => '123',
        ], $object->getList());
    }

    public function testClear()
    {
        $object = new \twin\test\helper\Header;

        $object->add('one', 'one');
        $object->add('two', 'two');

        $this->assertSame([
            'one' => 'one',
            'two' => 'two',
        ], $object->getList());

        $object->clear();

        $this->assertSame([], $object->getList());
    }

    public function testGet()
    {
        $mock = $this->getMock();

        $this->assertSame('text/html;charset=UTF-8', $mock->get('Content-type'));
        $this->assertSame('max-age=604800', $mock->get('Cache-Control'));
        $this->assertSame('max-age=604800', $mock->get('cache-control')); // Регистронезависимый
        $this->assertNull($mock->get('not-exists'));
    }

    public function testGetList()
    {
        $mock = $this->getMockBuilder(Header::class)
            ->onlyMethods(['getRawList'])
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getRawList')
            ->willReturn(self::RAW_HEADERS);

        $expected = [
            'Content-type' => 'text/html;charset=UTF-8',
            'Cache-Control' => 'max-age=604800',
        ];

        $this->assertSame($expected, $mock->getList());
    }

    /**
     * @return MockObject|Header
     */
    private function getMock(): MockObject
    {
        $mock = $this->getMockBuilder(Header::class)
            ->onlyMethods(['getRawList'])
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getRawList')
            ->willReturn(self::RAW_HEADERS);

        return $mock;
    }
}
