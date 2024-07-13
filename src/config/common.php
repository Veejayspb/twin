<?php

use twin\response\Response;
use twin\route\RouteManager;

return [
    'name' => 'Twin application',
    'language' => 'en',
    'params' => [],
    'components' => [
        'router' => [
            'class' => RouteManager::class,
        ],
        'response' => [
            'class' => Response::class,
        ],
    ],
];
