<?php

use twin\asset\AssetManager;
use twin\helper\Request;
use twin\session\Session;
use twin\view\View;

return [
    'parent' => '@twin/config/common.php',
    'components' => [
        'route' => [
            'namespaces' => [
                '' => 'app\\controller',
            ],
            'rules' => [
                '/' => 'site/index',
                '/<controller:[a-z\-]+>' => '<controller>/index',
                '/<controller:[a-z\-]+>/<id:[0-9]+>' => '<controller>/view',
                '/<controller:[a-z\-]+>/<action:[a-z\-]+>' => '<controller>/<action>',
                '/<module:[a-z\-]+>/<controller:[a-z\-]+>/<action:[a-z\-]+>' => '<module>/<controller>/<action>',
            ],
            'domain' => Request::$scheme . '://' . Request::$host,
        ],
        'view' => [
            'class' => View::class,
        ],
        'asset' => [
            'class' => AssetManager::class,
            'publicationPath' => '@web/asset',
            'webPath' => '/asset',
        ],
        'session' => [
            'class' => Session::class,
        ],
    ],
];
