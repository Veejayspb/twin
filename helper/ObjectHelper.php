<?php

namespace twin\helper;

use ReflectionClass;
use ReflectionProperty;

class ObjectHelper
{
    /**
     * Заполнить свойства объекта.
     * @param object $object - исходный объект
     * @param array $properties - публичные нестатические свойства
     * @return object
     */
    public static function setProperties(object $object, array $properties): object
    {
        foreach ($properties as $name => $value) {
            if (!static::isPublicProperty($object, $name)) {
                continue;
            }

            $object->$name = $value;
        }

        return $object;
    }

    /**
     * Имеется ли у объекта публичное свойство.
     * @param object $object
     * @param string $property
     * @return bool
     */
    public static function isPublicProperty(object $object, string $property): bool
    {
        $reflectionProperty = static::getProperty($object, $property);

        if (!$reflectionProperty) {
            return false;
        }

        return $reflectionProperty->isPublic();
    }

    /**
     * Вернуть reflection-свойство объекта.
     * @param object $object
     * @param string $property
     * @return ReflectionProperty|null
     */
    protected static function getProperty(object $object, string $property): ?ReflectionProperty
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        $reflection = new ReflectionClass($object);
        return $reflection->getProperty($property);
    }
}
