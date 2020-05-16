<?php

use twin\route\RouteManager;

$config = [
    'components' => [
        'route' => [
            'class' => RouteManager::class,
            'namespaces' => [
                '' => 'app\\command',
            ],
        ],
    ],
];

return array_replace_recursive(
    include __DIR__ . DIRECTORY_SEPARATOR . '/common.php',
    $config
);
