<?php

namespace twin\type;

class TypeString extends Type
{
    /**
     * {@inheritdoc}
     * @return string
     */
    public function set($value)
    {
        return (string)$value;
    }
}
