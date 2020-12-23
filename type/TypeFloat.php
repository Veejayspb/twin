<?php

namespace twin\type;

class TypeFloat extends Type
{
    /**
     * {@inheritdoc}
     * @return float
     */
    public function set($value)
    {
        return (float)$value;
    }
}
