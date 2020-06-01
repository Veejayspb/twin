<?php

use twin\migration\MigrationManager;
use twin\route\RouteManager;

return [
    'parent' => __DIR__ . DIRECTORY_SEPARATOR . 'common.php',
    'components' => [
        'route' => [
            'class' => RouteManager::class,
            'namespaces' => [
                '' => 'app\\command',
                'migration' => 'twin\\controller',
            ],
            'rules' => [
                'migration' => 'migration/migration/help',
                'migration/<action:[a-z]+>' => 'migration/migration/<action>',
                '<controller:[a-z]+>/<action:[a-z]+>' => '<controller>/<action>',
            ],
        ],
        'migration' => [
            'class' => MigrationManager::class,
            'path' => '@app/command/migration',
        ],
    ],
];
