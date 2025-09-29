<?php

use twin\migration\MigrationManager;
use twin\response\ResponseConsole;
use twin\route\RouteManager;
use twin\Twin;

require __DIR__ . DIRECTORY_SEPARATOR . 'common.php';

$di = Twin::app()->di;

$di->set('router', function () {
    $router = new RouteManager;
    $router->namespaces = [
        '' => 'console\\controller',
        'service' => 'twin\\controller',
    ];
    $router->rules = [
        'migration' => 'service/migration/help',
        'migration/<action:[a-z]+>' => 'service/migration/<action>',
        '<controller:[a-z]+>/<action:[a-z]+>' => '<controller>/<action>',
    ];
    return $router;
});
$di->set('response', fn() => new ResponseConsole);
$di->set('migration', fn() => new MigrationManager);
