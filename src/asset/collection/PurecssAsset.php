<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Tag;

class PurecssAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public array $css = [
        'main' => 'https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css',
        'responsive' => 'https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/grids-responsive-min.css',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, int|string $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
