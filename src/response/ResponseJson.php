<?php

namespace twin\response;

class ResponseJson extends Response
{
    /**
     * {@inheritdoc}
     */
    protected array $headers = [
        'Content-type' => 'application/json',
    ];

    /**
     * {@inheritdoc}
     * @param mixed $data
     */
    public function run(mixed $data): string
    {
        $data = (array)$data;
        return json_encode($data);
    }
}
