<?php

return [
    'name' => 'Twin application',
    'language' => 'en',
    'params' => [],
    'aliases' => [
        '@root' => dirname(__DIR__, 2),
        '@app' => '@root/app',
        '@twin' => '@root/twin',
        '@web' => '@root/web',
    ],
];
