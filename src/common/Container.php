<?php

namespace twin\common;

use twin\asset\AssetManager;
use twin\migration\MigrationManager;
use twin\response\Response;
use twin\route\RouteManager;
use twin\session\Cookie;
use twin\session\Flash;
use twin\session\Identity;
use twin\session\Session;
use twin\view\View;

/**
 * Class Container
 *
 * @property-read RouteManager $router
 * @property-read Response $response
 * @property-read View $view
 * @property-read AssetManager $asset
 * @property-read Cookie $cookie
 * @property-read Session $session
 * @property-read MigrationManager $migration
 * @property-read Identity $identity
 * @property-read Flash $flash
 */
class Container extends \Veejay\Container\Container
{

}
