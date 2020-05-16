<?php

use twin\cache\CacheFile;
use twin\route\RouteManager;
use twin\session\Session;
use twin\view\View;

return [
    'name' => 'Twin application',
    'language' => 'ru',
    'params' => [],
    'aliases' => [
        '@root' => dirname(__DIR__, 2),
        '@app' => '@root/app',
        '@twin' => '@root/twin',
        '@web' => '@root/web',
    ],
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
        'cache' => [
            'class' => CacheFile::class,
        ],
    ],
];
