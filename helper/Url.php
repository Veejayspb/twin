<?php

namespace twin\helper;

use twin\route\Route;
use twin\route\RouteManager;
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
        $routeManager = static::getRouteManager();

        if (!$routeManager) {
            return null;
        }

        $route = $routeManager->getCurrentRoute();

        if (!$route) {
            return null;
        }

        $route = new Route($route->module, $route->controller, $route->action);
        $route->parse($strRoute);
        $route->params = $params;

        return $routeManager->createUrl($route, $absolute) ?: null;
    }

    /**
     * Создать адрес с текущим роутом.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function current(array $params = [], bool $absolute = false): ?string
    {
        $routeManager = static::getRouteManager();

        if (!$routeManager) {
            return null;
        }

        $route = $routeManager->getCurrentRoute();

        if (!$route) {
            return null;
        }

        $route->params = $params + $route->params;
        return $routeManager->createUrl($route, $absolute) ?: null;
    }

    /**
     * Адрес главной страницы.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function home(array $params = [], bool $absolute = false): ?string
    {
        $routeManager = static::getRouteManager();

        if (!$routeManager) {
            return null;
        }

        return static::to($routeManager->home, $params, $absolute);
    }

    /**
     * Адрес страницы login.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function login(array $params = [], bool $absolute = false): ?string
    {
        $routeManager = static::getRouteManager();

        if (!$routeManager) {
            return null;
        }

        return static::to($routeManager->login, $params, $absolute);
    }

    /**
     * Адрес страницы logout.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string|null
     */
    public static function logout(array $params = [], bool $absolute = false): ?string
    {
        $routeManager = static::getRouteManager();

        if (!$routeManager) {
            return null;
        }

        return static::to($routeManager->logout, $params, $absolute);
    }

    /**
     * Вернуть роутер для генерации адреса.
     * @return RouteManager|null
     */
    protected static function getRouteManager(): ?RouteManager
    {
        return Twin::app()->findComponent(RouteManager::class);
    }
}
