<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class FancyBoxAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $css = [
        'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css',
    ];

    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        JqueryAsset::class,
    ];
}
