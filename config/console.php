<?php

use twin\migration\MigrationManager;

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'route' => [
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
