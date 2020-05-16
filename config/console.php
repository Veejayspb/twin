<?php

use twin\route\RouteManager;

return [
    'name' => 'Twin console application',
    'language' => 'ru',
    'params' => [],
    'aliases' => [
        '@root' => dirname(__DIR__, 2),
        '@app' => '@root/app',
        '@twin' => '@root/twin',
        '@web' => '@root/web',
    ],
    'components' => [
        'route' => [
            'class' => RouteManager::class,
            'namespaces' => [
                '' => 'app\\command',
            ],
        ],
    ],
];
