<?php

use twin\helper\Alias;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;

final class AliasTest extends BaseTestCase
{
    /**
     * @var ObjectProxy|Alias
     */
    protected $object;

    public function testSet()
    {
        $result = $this->object->set('@a', 'aaa');
        $data['@a'] = 'aaa';
        $this->assertTrue($result);
        $this->assertSame($data, $this->object->aliases);

        $result = $this->object->set('@b', 'bbb');
        $data['@b'] = 'bbb';
        $this->assertTrue($result);
        $this->assertSame($data, $this->object->aliases);

        $result = $this->object->set('@a', '');
        $data['@a'] = '';
        $this->assertTrue($result);
        $this->assertSame($data, $this->object->aliases);

        $result = $this->object->set('@not-allowed', 'na');
        $this->assertFalse($result);
        $this->assertSame($data, $this->object->aliases);

        $result = $this->object->set('@a', '@a/test/path');
        $this->assertFalse($result);
        $this->assertSame($data, $this->object->aliases);
    }

    public function testGet()
    {
        $this->object->aliases = [
            '@a' => 'aaa',
            '@b' => 'bbb',
            '@c' => '@b/ccc',
        ];

        $actual = $this->object->get('@a');
        $this->assertSame('aaa', $actual);

        $actual = $this->object->get('@b/yyy');
        $this->assertSame('bbb/yyy', $actual);

        $actual = $this->object->get('@c');
        $this->assertSame('bbb/ccc', $actual);

        $actual = $this->object->get('@notexists');
        $this->assertSame('@notexists', $actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ObjectProxy(new Alias);
        $this->object->aliases = [];
    }
}
