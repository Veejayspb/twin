<?php

namespace twin\type;

class TypeJson extends Type
{
    /**
     * {@inheritdoc}
     * @return array
     */
    public function set($value)
    {
        return (array)json_decode((string)$value, true);
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function get()
    {
        $value = parent::get();
        return (string)json_encode((array)$value);
    }
}
