<?php

use twin\asset\AssetManager;
use twin\helper\Request;
use twin\response\ResponseHtml;
use twin\route\RouteManager;
use twin\session\Identity;
use twin\session\Session;
use twin\Twin;
use twin\view\View;

require __DIR__ . DIRECTORY_SEPARATOR . 'common.php';

$di = Twin::app()->di;

$di->set('router', function () {
    $router = new RouteManager;
    $router->namespaces = [
        '' => 'app\\controller',
    ];
    $router->rules = [
        '/' => 'main/index',
        '/<controller:[a-z\-]+>' => '<controller>/index',
        '/<controller:[a-z\-]+>/<id:[0-9]+>' => '<controller>/view',
        '/<controller:[a-z\-]+>/<action:[a-z\-]+>' => '<controller>/<action>',
        '/<module:[a-z\-]+>/<controller:[a-z\-]+>/<action:[a-z\-]+>' => '<module>/<controller>/<action>',
    ];
    $router->domain = Request::$scheme . '://' . Request::$host;
    return $router;
});
$di->set('response', fn() => new ResponseHtml);
$di->set('view', fn() => new View);
$di->set('asset', fn() => new AssetManager);
$di->set('session', fn() => new Session);
$di->set('identity', fn() => new Identity($di->session, 'secret string'));
