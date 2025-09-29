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
