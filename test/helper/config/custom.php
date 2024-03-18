<?php

use twin\view\View;

return [
    'parent' =>'@test/helper/config/web.php',
    'name' => 'custom',
    'params' => [],
    'components' => [
        'route' => [
            'namespaces' => [
                '' => 'app\\controller',
            ],
        ],
        'view' => [
            'class' => View::class,
        ],
    ],
];
