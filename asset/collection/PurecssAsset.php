<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Tag;

class PurecssAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $css = [
        'main' => 'https://cdn.jsdelivr.net/npm/purecss@2.1.0/build/pure-min.css',
        'responsive' => 'https://cdn.jsdelivr.net/npm/purecss@2.1.0/build/grids-responsive-min.css',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-yHIFVG6ClnONEA5yB5DJXfW2/KC173DIQrYoZMEtBvGzmf0PKiGyNEqe9N6BNDBH';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
