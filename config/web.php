<?php

use core\route\RouteManager;
use core\session\Session;
use core\view\View;

return [
    'name' => 'Twin Application',
    'language' => 'ru',
    'params' => [],
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
