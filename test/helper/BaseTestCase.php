<?php

namespace twin\test\helper;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
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

    /**
     * Универсальный конструктор MOCK-объекта.
     * @param string $class
     * @param string|null $className
     * @param array|null $construct
     * @param array $methods
     * @return object
     */
    protected function mock(string $class, ?string $className = null, ?array $construct = [], array $methods = []): object
    {
        $reflection = new ReflectionClass($class);
        $builder = $this->getMockBuilder($class);

        if ($className !== null) {
            $builder->setMockClassName($className);
        }

        if ($construct === null) {
            $builder->disableOriginalConstructor();
        } else {
            $builder->setConstructorArgs($construct);
        }

        if (!empty($methods)) {
            $builder->onlyMethods(array_keys($methods));
        }

        if ($reflection->isAbstract()) {
            $mock = $builder->getMockForAbstractClass();
        } elseif ($reflection->isTrait()) {
            $mock = $builder->getMockForTrait();
        } else {
            $mock = $builder->getMock();
        }

        foreach ($methods as $name => $return) {
            $method = $mock
                ->expects($this->any())
                ->method($name);

            if (is_callable($return)) {
                $method->willReturnCallback($return);
            } else {
                $method->willReturn($return);
            }
        }

        return $mock;
    }
}
