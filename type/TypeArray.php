<?php

namespace twin\type;

class TypeArray extends Type
{
    /**
     * {@inheritdoc}
     * @return array
     */
    public function set($value)
    {
        return (array)$value;
    }
}
