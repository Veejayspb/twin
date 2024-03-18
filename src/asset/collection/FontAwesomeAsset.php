<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Tag;

class FontAwesomeAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $css = [
        'main' => 'https://use.fontawesome.com/releases/v5.8.1/css/all.css',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
