<?php

use twin\response\Response;
use twin\route\RouteManager;
use twin\Twin;

$twin = Twin::app();
$twin->name = 'Twin application';
$twin->language = 'ru';
$twin->params = [];

$twin->di->set('router', fn() => new RouteManager);
$twin->di->set('response', fn() => new Response);
