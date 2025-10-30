<?php

namespace twin\helper;

use twin\route\Route;
use twin\Twin;

class Url
{
    /**
     * Создать адрес.
     * @param string $strRoute - текстовый роут
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function to(string $strRoute, array $params = [], bool $absolute = false): ?string
    {
        $router = Twin::app()->di->get('router');
        $route = $router->getCurrentRoute();

        if (!$route) {
            return null;
        }

        $route = new Route($route->module, $route->controller, $route->action);
        $route->parse($strRoute);
        $route->params = $params;

        return $router->createUrl($route, $absolute) ?: null;
    }

    /**
     * Создать адрес с текущим роутом.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function current(array $params = [], bool $absolute = false): ?string
    {
        $router = Twin::app()->di->get('router');
        $route = $router->getCurrentRoute();

        if (!$route) {
            return null;
        }

        $route->params = $params + $route->params;
        return $router->createUrl($route, $absolute) ?: null;
    }

    /**
     * Адрес главной страницы.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function home(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->di->get('router')->home, $params, $absolute);
    }

    /**
     * Адрес страницы login.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function login(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->di->get('router')->login, $params, $absolute);
    }

    /**
     * Адрес страницы logout.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function logout(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->di->get('router')->logout, $params, $absolute);
    }
}
