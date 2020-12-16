<?php

use twin\migration\MigrationManager;

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'route' => [
            'namespaces' => [
                '' => 'app\\command',
                'service' => 'twin\\controller',
            ],
            'rules' => [
                'migration' => 'service/migration/help',
                'migration/<action:[a-z]+>' => 'service/migration/<action>',
                '<controller:[a-z]+>/<action:[a-z]+>' => '<controller>/<action>',
            ],
        ],
        'migration' => [
            'class' => MigrationManager::class,
            'path' => '@app/command/migration',
        ],
    ],
];
