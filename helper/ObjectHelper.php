<?php

namespace twin\helper;

use ReflectionClass;
use ReflectionProperty;

class ObjectHelper
{
    /**
     * Исходный объект.
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
     * Заполнить свойства объекта.
     * @param array $properties - публичные нестатические свойства
     * @return object
     */
    public function setProperties(array $properties): object
    {
        foreach ($properties as $name => $value) {
            if (!$this->isPublicProperty($name)) {
                continue;
            }

            $this->object->$name = $value;
        }

        return $this->object;
    }

    /**
     * Имеется ли у объекта публичное свойство.
     * @param string $property
     * @return bool
     */
    public function isPublicProperty(string $property): bool
    {
        $reflectionProperty = $this->getProperty($property);

        if (!$reflectionProperty) {
            return false;
        }

        return $reflectionProperty->isPublic();
    }

    /**
     * Имеется ли у объекта защищенное свойство.
     * @param string $property
     * @return bool
     */
    public function isProtectedProperty(string $property): bool
    {
        $reflectionProperty = $this->getProperty($property);

        if (!$reflectionProperty) {
            return false;
        }

        return $reflectionProperty->isProtected();
    }

    /**
     * Имеется ли у объекта приватное свойство.
     * @param string $property
     * @return bool
     */
    public function isPrivateProperty(string $property): bool
    {
        $reflectionProperty = $this->getProperty($property);

        if (!$reflectionProperty) {
            return false;
        }

        return $reflectionProperty->isPrivate();
    }

    /**
     * Является ли свойство статическим.
     * @param string $property
     * @return bool
     */
    public function isStaticProperty(string $property): bool
    {
        $reflectionProperty = $this->getProperty($property);

        if (!$reflectionProperty) {
            return false;
        }

        return $reflectionProperty->isStatic();
    }

    /**
     * Вернуть reflection-свойство объекта.
     * @param string $property
     * @return ReflectionProperty|null
     */
    protected function getProperty(string $property): ?ReflectionProperty
    {
        if (!property_exists($this->object, $property)) {
            return null;
        }

        $reflection = new ReflectionClass($this->object);
        return $reflection->getProperty($property);
    }
}
