<?php

namespace twin\test\helper;

use PHPUnit\Framework\TestCase;
use Throwable;

abstract class BaseTestCase extends TestCase
{
    /**
     * Содержится ли подстрока в строке.
     * @param string $haystack
     * @param string $needle
     * @param string $message
     * @return void
     */
    protected function assertStringContains(string $haystack, string $needle, string $message = ''): void
    {
        $result = strstr($haystack, $needle);
        $this->assertTrue($result !== false, $message ?: "String isn't contains the needle: $needle");
    }

    /**
     * Содержится ли строка в файле.
     * @param string $path
     * @param string $needle
     * @param string $message
     * @return void
     */
    protected function assertFileContains(string $path, string $needle, string $message = ''): void
    {
        if (!is_file($path)) {
            return;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return;
        }

        $this->assertStringContains($content, $needle, $message ?: "File isn't contains the needle: $needle");
    }

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
