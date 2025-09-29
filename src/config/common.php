<?php

use twin\helper\Params;
use twin\i18n\I18n;
use twin\response\Response;
use twin\route\RouteManager;
use twin\Twin;

$di = Twin::app()->di;

$di->set('params', fn() => new Params([
    'name' => 'Twin application',
    'language' => 'ru',
]));
$di->set('router', fn() => new RouteManager);
$di->set('response', fn() => new Response);
$di->set('i18n', fn() => new I18n);
