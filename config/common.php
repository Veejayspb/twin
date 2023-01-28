<?php

use twin\response\Response;
use twin\route\RouteManager;

return [
    'name' => 'Twin application',
    'language' => 'en',
    'params' => [],
    'components' => [
        'route' => [
            'class' => RouteManager::class,
        ],
        'response' => [
            'class' => Response::class,
        ],
    ],
];
