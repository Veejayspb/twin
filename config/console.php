<?php

use twin\migration\MigrationManagerFile;

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
            'class' => MigrationManagerFile::class,
        ],
    ],
];
