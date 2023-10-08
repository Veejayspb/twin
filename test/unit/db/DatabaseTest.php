<?php

use PHPUnit\Framework\MockObject\MockObject;
use twin\db\Database;
use twin\test\helper\BaseTestCase;

final class DatabaseTest extends BaseTestCase
{
    public function testConstruct()
    {
        $code = $this->catchExceptionCode(function () {
            $database = $this->getDatabase(true);
            $database->__construct();
        });

        $this->assertSame(0, $code);

        $code = $this->catchExceptionCode(function () {
            $database = $this->getDatabase(false);
            $database->__construct();
        });

        $this->assertSame(500, $code);
    }

    /**
     * @param bool $connect
     * @return MockObject|Database
     */
    protected function getDatabase(bool $connect)
    {
        $mock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['connect'])
            ->getMockForAbstractClass();

        $mock
            ->expects($this->any())
            ->method('connect')
            ->willReturn($connect);

        return $mock;
    }
}
