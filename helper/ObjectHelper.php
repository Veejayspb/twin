<?php

namespace twin\helper;

use twin\common\Exception;

class ObjectHelper
{
    /**
     * Заполнить свойства объекта.
     * @param mixed $object - исходный объект
     * @param array $properties - публичные нестатические свойства
     * @return mixed
     * @throws Exception
     */
    public static function setProperties($object, array $properties)
    {
        if (!is_object($object)) {
            throw new Exception(500, 'Wrong param type: ' . gettype($object));
        }

        foreach ($properties as $name => $value) {
            if (!property_exists($object, $name)) {
                continue;
            }
            $object->$name = $value;
        }

        return $object;
    }
}
