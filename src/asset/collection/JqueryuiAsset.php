<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class JqueryuiAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public array $css = [
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
    ];

    /**
     * {@inheritdoc}
     */
    public array $js = [
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public array $depends = [
        JqueryAsset::class,
    ];
}
