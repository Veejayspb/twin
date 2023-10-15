<?php

use twin\db\Database;
use twin\test\helper\BaseTestCase;

final class DatabaseTest extends BaseTestCase
{
    public function testConstruct()
    {
        $code = $this->catchExceptionCode(function () {
            $database = $this->mock(Database::class, null, null, ['connect' => true]);
            $database->__construct();
        });

        $this->assertSame(0, $code);

        $code = $this->catchExceptionCode(function () {
            $database = $this->mock(Database::class, null, null, ['connect' => false]);
            $database->__construct();
        });

        $this->assertSame(500, $code);
    }
}
