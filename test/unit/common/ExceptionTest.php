<?php

use twin\common\Exception;
use twin\test\helper\BaseTestCase;

final class ExceptionTest extends BaseTestCase
{
    public function testConstruct()
    {
        $exception = new Exception(301);
        $this->assertSame(301, $exception->getCode());
        $this->assertSame('Moved permanently', $exception->getMessage());

        $exception = new Exception(302, null);
        $this->assertSame(302, $exception->getCode());
        $this->assertSame('Moved temporarily', $exception->getMessage());

        $exception = new Exception(303, '');
        $this->assertSame(303, $exception->getCode());
        $this->assertSame('See other', $exception->getMessage());

        $exception = new Exception(400, 'Bad request');
        $this->assertSame(400, $exception->getCode());
        $this->assertSame('Bad request', $exception->getMessage());

        $exception = new Exception(0, 'Not exists');
        $this->assertSame(0, $exception->getCode());
        $this->assertSame('Not exists', $exception->getMessage());

        $exception = new Exception(0);
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getMessage());
    }
}
