<?php

namespace twin\helper;

use ReflectionClass;

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
            if (!property_exists($object, $name)) {
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
        if (!property_exists($object, $property)) {
            return false;
        }

        $reflection = new ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);

        return $reflectionProperty->isPublic();
    }
}
