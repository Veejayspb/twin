<?php

namespace twin\validator;

class Boolean extends Integer
{
    /**
     * {@inheritdoc}
     */
    public $min = 0;

    /**
     * {@inheritdoc}
     */
    public $max = 1;
}
