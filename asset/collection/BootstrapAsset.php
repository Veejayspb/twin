<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Tag;

class BootstrapAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $css = [
        'main' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css',
    ];

    /**
     * {@inheritdoc}
     */
    public $js = [
        'main' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        JqueryAsset::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareJs(Tag $tag, $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
