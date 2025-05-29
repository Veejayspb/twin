<?php

namespace twin\response;

class ResponseHtml extends Response
{
    /**
     * {@inheritdoc}
     */
    protected array $headers = [
        'Content-type' => 'text/html',
    ];
}
