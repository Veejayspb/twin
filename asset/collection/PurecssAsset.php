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
        'main' => 'https://unpkg.com/purecss@2.0.3/build/pure-min.css',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
