<?php

use twin\route\RouteManager;
use twin\session\Session;
use twin\view\View;

return [
    'parent' => __DIR__ . DIRECTORY_SEPARATOR . 'common.php',
    'components' => [
        'route' => [
            'class' => RouteManager::class,
            'namespaces' => [
                '' => 'app\\controller',
            ],
            'rules' => [
                '/' => 'site/index',
                '/<controller:[a-z]+>' => '<controller>/index',
                '/<controller>/<id:[0-9]+>' => '<controller>/view',
                '/<controller:[a-z]+>/<action:[a-z]+>' => '<controller>/<action>',
            ],
        ],
        'view' => [
            'class' => View::class,
        ],
        'session' => [
            'class' => Session::class,
        ],
    ],
];
