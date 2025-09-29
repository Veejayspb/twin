<?php

use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use test\helper\Twin as TwinChild;
use twin\Twin;
use twin\view\View;

final class TwinTest extends BaseTestCase
{
    public function testApp()
    {
        $this->assertSame(TwinChild::app(), TwinChild::app());
        $this->assertSame(Twin::app(), TwinChild::app());
        $this->assertSame(get_class(Twin::app()), TwinChild::class);
        $this->assertSame(get_class(TwinChild::app()), TwinChild::class);
    }

    public function testParam()
    {
        Twin::app()->params = [
            'one-1',
            'one-2' => 2,
            'one-3' => [
                'two-1' => false,
                'two-2' => [
                    'three-1' => '321',
                    'three-2' => null,
                    'three-3' => $std = new stdClass,
                ],
            ],
        ];

        $this->assertSame(null, Twin::param('notexists'));
        $this->assertSame('default', Twin::param('notexists', 'default'));

        $this->assertSame('one-1', Twin::param('0'));
        $this->assertSame(2, Twin::param('one-2', 'default'));
        $this->assertSame(false, Twin::param('one-3.two-1'));
        $this->assertSame('321', Twin::param('one-3.two-2.three-1'));
        $this->assertSame(null, Twin::param('one-3.two-2.three-2'));
        $this->assertSame($std, Twin::param('one-3.two-2.three-3'));
    }

    public function testDate()
    {
        $this->assertSame(Twin::date(), Twin::date());
    }

    public function testImport()
    {
        $actual = Twin::import('notexists', true);
        $this->assertFalse($actual);

        $actual = Twin::import('notexists', false);
        $this->assertFalse($actual);

        $actual = Twin::import(__FILE__, true);
        $this->assertTrue($actual);

        $actual = Twin::import('@test/helper/config/common.php', false);
        $this->assertIsArray($actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        TwinChild::resetSingletone();
    }
}
