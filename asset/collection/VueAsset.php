<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class VueAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://cdn.jsdelivr.net/npm/vue',
    ];
}
