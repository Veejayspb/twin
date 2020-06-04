<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class JqueryAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js',
    ];
}
