<?php

namespace twin\helper;

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
}
