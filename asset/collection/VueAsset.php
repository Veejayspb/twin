<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class VueAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://unpkg.com/vue@3.0.4/dist/vue.global.js',
    ];
}
