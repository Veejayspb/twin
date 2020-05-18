<?php

use twin\cache\CacheFile;
use twin\route\RouteManager;
use twin\session\Session;
use twin\view\View;

$config = [
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

return array_replace_recursive(
    include __DIR__ . DIRECTORY_SEPARATOR . '/common.php',
    $config
);
