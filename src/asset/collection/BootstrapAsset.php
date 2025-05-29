<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Tag;

class BootstrapAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public array $css = [
        'main' => 'https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css',
    ];

    /**
     * {@inheritdoc}
     */
    public array $js = [
        'main' => 'https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js',
    ];

    /**
     * {@inheritdoc}
     */
    public array $depends = [
        JqueryAsset::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareCss(Tag $tag, int|string $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareJs(Tag $tag, int|string $key): Tag
    {
        if ($key == 'main') {
            $tag->integrity = 'sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd';
            $tag->crossorigin = 'anonymous';
        }
        return $tag;
    }
}
