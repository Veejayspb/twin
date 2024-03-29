<?php

use twin\migration\MigrationManager;

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'route' => [
            'namespaces' => [
                '' => 'console\\controller',
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
        ],
    ],
];