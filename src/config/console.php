<?php

use twin\migration\MigrationManager;
use twin\response\ResponseConsole;

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'router' => [
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
        'response' => [
            'class' => ResponseConsole::class,
        ],
        'migration' => [
            'class' => MigrationManager::class,
        ],
    ],
];
