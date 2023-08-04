<?php

namespace twin\test\helper;

use ReflectionClass;

class ObjectProxy
{
    /**
     * @var object
     */
    protected $object;

    /**
     * @param object $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
    }

    /**
     * @param string $name
     * @return mixed|false
     */
    public function __get($name)
    {
        $reflection = new ReflectionClass($this->object);

        if (!$reflection->hasProperty($name)) {
            return false;
        }

        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($this->object);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $reflection = new ReflectionClass($this->object);

        if (!$reflection->hasProperty($name)) {
            return;
        }

        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->object, $value);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed|false
     */
    public function __call($name, $arguments)
    {
        $className = get_class($this->object);
        $reflection = new ReflectionClass($className);

        if (!$reflection->hasMethod($name)) {
            return false;
        }

        $method = $reflection->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->object, $arguments);
    }
}
