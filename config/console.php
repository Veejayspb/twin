<?php

use twin\route\RouteManager;

return [
    'name' => 'Twin Application',
    'language' => 'ru',
    'params' => [],
    'components' => [
        'route' => [
            'class' => RouteManager::class,
            'namespaces' => [
                '' => 'app\\command',
            ],
        ],
    ],
];
