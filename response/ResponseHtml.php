<?php

namespace twin\response;

class ResponseHtml extends Response
{
    /**
     * {@inheritdoc}
     */
    protected $headers = [
        'Content-type' => 'text/html',
    ];
}
