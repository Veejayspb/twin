<?php

use twin\helper\Tag;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;

final class TagTest extends BaseTestCase
{
    const ATTRIBUTES = [
        'attr' => 1,
        'Attr' => 2,
        '#' => 'test',
        '' => '',
        'q' => null,
        'w' => false,
        'e' => true,
        null => 1,
        false => '2',
        true => 3,
        'a_b' => [],
        1 => 'qwerty',
        'a b c' => 0b1,
    ];

    public function testConstruct()
    {
        $tag = new Tag('a', ['attr' => 1], 'test-content');
        $proxy = new ObjectProxy($tag);

        $this->assertSame('a', $proxy->name);
        $this->assertSame(['attr' => 1], $proxy->attributes);
        $this->assertSame('test-content', $proxy->content);
    }

    public function testToString()
    {
        $tag = new Tag('div', ['onchange' => 'test1', 'lang' => 'ru'], 'test-content');
        $this->assertSame('<div onchange="test1" lang="ru">test-content</div>', (string)$tag);

        $tag = new Tag('hr', ['class' => 'separator'], 'inactive content');
        $this->assertSame('<hr class="separator">', (string)$tag);
    }

    public function testOpen()
    {
        $items = [
            [
                'name' => 'a',
                'attributes' => ['href' => '#'],
                'result' => '<a href="#">',
            ],
            [
                'name' => 'table',
                'attributes' => ['aria-colspan' => 100, 'aria-controls' => 'eee'],
                'result' => '<table aria-colspan="100" aria-controls="eee">',
            ],
            [
                'name' => 'qwerty',
                'attributes' => ['w' => null, 'e' => false],
                'result' => '<qwerty>',
            ],
        ];

        foreach ($items as $item) {
            $tag = new Tag($item['name'], $item['attributes']);
            $this->assertSame($item['result'], $tag->open());
        }
    }

    public function testClose()
    {
        $items = [
            'a' => '</a>',
            'br' => '</br>',
            'test' => '</test>',
        ];

        foreach ($items as $name => $result) {
            $tag = new Tag($name);
            $this->assertSame($result, $tag->close());
        }
    }

    public function testSetName()
    {
        $items = [
            'table' => 'table',
            'tr' => 'tr',
            '' => 'undefined',
        ];

        foreach ($items as $name => $result) {
            $tag = new Tag('test');
            $tag->setName($name);
            $proxy = new ObjectProxy($tag);
            $this->assertSame($result, $proxy->name);
        }
    }

    public function testGetName()
    {
        $items = ['a', 'br', 'div'];

        foreach ($items as $item) {
            $tag = new Tag($item);
            $this->assertSame($item, $tag->getName());
        }
    }

    public function testSetAttribute()
    {
        $invalidNames = [
            'Name',
            ['name'],
            'some_name',
            7,
            '#',
        ];

        foreach ($invalidNames as $invalidName) {
            $tag = new Tag('test');
            $result = $tag->setAttribute($invalidName, 'value');
            $this->assertFalse($result);
        }

        $validNames = [
            'name',
            'some-name',
        ];

        foreach ($validNames as $validName) {
            $tag = new Tag('test');
            $result = $tag->setAttribute($validName, 'value');
            $this->assertTrue($result);
        }

        $nullValues = [
            null,
            false,
        ];

        foreach ($nullValues as $nullValue) {
            $tag = new Tag('test');
            $proxy = new ObjectProxy($tag);
            $proxy->attributes = ['name' => 'value'];
            $result = $tag->setAttribute('name', $nullValue);
            $this->assertTrue($result);
            $this->assertSame([], $proxy->attributes);
        }

        $invalidValues = [
            18.4,
            ['value'],
            new stdClass,
        ];

        foreach ($invalidValues as $invalidValue) {
            $tag = new Tag('test');
            $result = $tag->setAttribute('name', $invalidValue);
            $this->assertFalse($result);
        }

        $values = [
            'test value',
            'test-value',
            '_value_',
            123,
            '',
            ' ',
            true,
        ];

        foreach ($values as $value) {
            $tag = new Tag('test');
            $proxy = new ObjectProxy($tag);
            $result = $tag->setAttribute('name', $value);
            $this->assertTrue($result);
            $this->assertSame(['name' => $value], $proxy->attributes);
        }
    }

    public function testSetAttributes()
    {
        $tag = new Tag('name');
        $proxy = new ObjectProxy($tag);
        $tag->setAttributes(self::ATTRIBUTES);

        $this->assertSame([
            'attr' => 1,
            'e' => true,
        ], $proxy->attributes);

        $tag->setAttributes(['q' => 'test']);
        $this->assertSame(['q' => 'test'], $proxy->attributes);
    }

    public function testGetAttribute()
    {
        $tag = new Tag('test', self::ATTRIBUTES);

        $this->assertSame(1, $tag->getAttribute('attr'));
        $this->assertTrue($tag->getAttribute('e'));
        $this->assertNull($tag->getAttribute('not-exists'));
    }

    public function testGetAttributes()
    {
        $tag = new Tag('test');
        $proxy = new ObjectProxy($tag);
        $proxy->attributes = self::ATTRIBUTES;

        $this->assertSame(self::ATTRIBUTES, $tag->getAttributes());
    }

    public function testSetContent()
    {
        $items = ['qwerty', '', ' '];

        foreach ($items as $item) {
            $tag = new Tag('test');
            $proxy = new ObjectProxy($tag);
            $tag->setContent($item);

            $this->assertSame($item, $proxy->content);
        }
    }

    public function testGetContent()
    {
        $items = ['qwerty', '', ' '];

        foreach ($items as $item) {
            $tag = new Tag('test');
            $proxy = new ObjectProxy($tag);
            $proxy->content = $item;

            $this->assertSame($item, $tag->getContent());
        }
    }

    public function testIsSingle()
    {
        $items = [
            'a' => false,
            'br' => true,
            'hr' => true,
            'b' => false,
            'test' => false,
        ];

        foreach ($items as $name => $isSingle) {
            $tag = new Tag($name);
            $this->assertSame($isSingle, $tag->isSingle());
        }
    }
}
