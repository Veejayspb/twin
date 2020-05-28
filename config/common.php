<?php

return [
    'name' => 'Twin application',
    'language' => 'en',
    'params' => [],
    'aliases' => [
        '@root' => dirname(__DIR__, 2),
        '@twin' => dirname(__DIR__),
        '@app' => '@root/app',
        '@runtime' => '@app/runtime',
        '@web' => '@root/web',
    ],
];
