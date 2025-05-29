<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class FancyBoxAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public array $css = [
        'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css',
    ];

    /**
     * {@inheritdoc}
     */
    public array $js = [
        'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public array $depends = [
        JqueryAsset::class,
    ];
}
