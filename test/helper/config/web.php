<?php

use twin\route\RouteManager;

return [
    'parent' => '@test/helper/config/common.php',
    'name' => 'web',
    'language' => 'ru',
    'params' => [
        'key' => 'value',
    ],
    'components' => [
        'route' => [
            'class' => RouteManager::class,
        ],
    ],
];
