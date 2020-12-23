<?php

namespace twin\type;

class TypeBool extends Type
{
    /**
     * {@inheritdoc}
     * @return bool
     */
    public function set($value)
    {
        return (bool)$value;
    }

    /**
     * {@inheritdoc}
     * @return int
     */
    public function get()
    {
        $value = parent::get();
        return (int)(bool)$value;
    }
}
