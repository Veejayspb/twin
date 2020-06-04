<?php

namespace twin\asset\collection;

use twin\asset\Asset;

class JqueryuiAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $css = [
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
    ];

    /**
     * {@inheritdoc}
     */
    public $js = [
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        JqueryAsset::class,
    ];
}
