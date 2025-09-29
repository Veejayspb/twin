<?php

use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use test\helper\Twin as TwinChild;
use twin\Twin;
use twin\view\View;

final class TwinTest extends BaseTestCase
{
    public function testGet()
    {
        $twin = Twin::app();
        $view = new View;

        $proxy = new ObjectProxy($twin);
        $proxy->components = ['view' => $view];

        $this->assertNull($twin->notexists);
        $this->assertNull($twin->VIEW);
        $this->assertSame($view, $twin->view);
    }

    public function testApp()
    {
        $this->assertSame(TwinChild::app(), TwinChild::app());
        $this->assertSame(Twin::app(), TwinChild::app());
        $this->assertSame(get_class(Twin::app()), TwinChild::class);
        $this->assertSame(get_class(TwinChild::app()), TwinChild::class);
    }

    /*public function testRun()
    {
        // TODO...
    }*/

    public function testGetComponents()
    {
        $twin = Twin::app();
        $proxy = new ObjectProxy($twin);

        $proxy->components = [];
        $this->assertSame($proxy->components, $twin->getComponents());

        $proxy->components = ['test' => new View];
        $this->assertSame($proxy->components, $twin->getComponents());
    }

    public function testGetComponent()
    {
        $twin = Twin::app();
        $proxy = new ObjectProxy($twin);

        $view = new View;
        $proxy->components = ['view' => $view];

        $this->assertNull($twin->getComponent('notexists'));
        $this->assertNull($twin->getComponent('VIEW'));
        $this->assertSame($view, $twin->getComponent('view'));
    }

    public function testSetComponent()
    {
        $twin = Twin::app();
        $proxy = new ObjectProxy($twin);
        $expected = [];

        $this->assertSame($expected, $proxy->components);

        $view1 = new View;
        $view2 = new View;

        $twin->setComponent('test', $view1);
        $expected['test'] = $view1;
        $this->assertSame($expected, $proxy->components);

        $twin->setComponent('test', $view2);
        $expected['test'] = $view2;
        $this->assertSame($expected, $proxy->components);

        $twin->setComponent('test', null);
        unset($expected['test']);
        $this->assertSame($expected, $proxy->components);
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
