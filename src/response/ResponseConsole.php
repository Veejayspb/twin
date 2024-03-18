<?php

namespace twin\response;

class ResponseConsole extends Response
{
    /**
     * {@inheritdoc}
     * @param array $data
     */
    public function run($data): string
    {
        return implode(PHP_EOL, (array)$data);
    }
}
