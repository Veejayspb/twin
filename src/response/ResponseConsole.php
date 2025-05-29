<?php

namespace twin\response;

class ResponseConsole extends Response
{
    /**
     * {@inheritdoc}
     * @param mixed $data
     */
    public function run(mixed $data): string
    {
        return implode(PHP_EOL, (array)$data);
    }
}
