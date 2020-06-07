<?php

namespace twin\validator;

class Boolean extends Integer
{
    /**
     * {@inheritdoc}
     */
    protected $min = 0;

    /**
     * {@inheritdoc}
     */
    protected $max = 1;
}
