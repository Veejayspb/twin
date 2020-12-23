<?php

namespace twin\type;

class TypeInt extends Type
{
    /**
     * {@inheritdoc}
     * @return int
     */
    public function set($value)
    {
        return (int)$value;
    }
}
