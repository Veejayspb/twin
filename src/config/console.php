<?php

use twin\migration\MigrationManager;
use twin\response\ResponseConsole;
use twin\route\RouteManager;
use twin\Twin;

require __DIR__ . DIRECTORY_SEPARATOR . 'common.php';

$twin = Twin::app();

$twin->di->set('router', function () {
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
$twin->di->set('response', fn() => new ResponseConsole);
$twin->di->set('migration', fn() => new MigrationManager);
