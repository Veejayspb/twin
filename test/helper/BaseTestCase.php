<?php

namespace twin\test\helper;

use PHPUnit\Framework\TestCase;
use Throwable;

abstract class BaseTestCase extends TestCase
{
    /**
     * Поймать исключение и вернуть его код.
     * @param callable $callback - код, бросающий исключение
     * @return int - 0 если исключение не брошено
     */
    protected function catchExceptionCode(callable $callback): int
    {
        try {
            $callback();
            return 0;
        } catch (Throwable $e) {
            return $e->getCode();
        }
    }
}
