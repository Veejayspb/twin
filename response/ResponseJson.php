<?php

namespace twin\response;

class ResponseJson extends Response
{
    /**
     * {@inheritdoc}
     */
    protected $headers = [
        'Content-type' => 'application/json',
    ];

    /**
     * {@inheritdoc}
     * @param array $data
     */
    public function run($data): string
    {
        $data = (array)$data;
        return json_encode($data);
    }
}
