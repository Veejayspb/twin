<?php

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'route' => [
            'namespaces' => [
                '' => 'app\\command',
                'service' => 'twin\\controller',
            ],
            'rules' => [
                '<controller:[a-z]+>/<action:[a-z]+>' => '<controller>/<action>',
            ],
        ],
    ],
];
