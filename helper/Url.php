<?php

namespace twin\helper;

use twin\controller\Controller;
use twin\route\Route;
use twin\route\RouteManager;
use twin\Twin;

class Url
{
    /**
     * Адрес домена.
     * @return string
     */
    public static function host(): string
    {
        return Request::$scheme . '://' . Request::$host;
    }

    /**
     * Создать адрес.
     * @param string $route - текстовый роут
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function to(string $route, array $params = [], bool $absolute = false): string
    {
        $r = Controller::$instance->route;
        $r = new Route($r->module, $r->controller, $r->action);
        $r->setRoute($route);
        $r->params = $params;
        $url = static::getRouter()->createUrl($r);

        if ($absolute) {
            $host = static::host();
            $url = $host . $url;
        }
        return $url;
    }

    /**
     * Создать адрес с текущим роутом.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function current(array $params = [], bool $absolute = false): string
    {
        $r = clone Controller::$instance->route;
        $r->params = $params + $r->params;
        $url = static::getRouter()->createUrl($r);

        if ($absolute) {
            $host = static::host();
            $url = $host . $url;
        }
        return $url;
    }

    /**
     * Адрес главной страницы.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function home(array $params = [], bool $absolute = false): string
    {
        return static::to(static::getRouter()->home, $params, $absolute);
    }

    /**
     * Адрес страницы login.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function login(array $params = [], bool $absolute = false): string
    {
        return static::to(static::getRouter()->login, $params, $absolute);
    }

    /**
     * Адрес страницы logout.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function logout(array $params = [], bool $absolute = false): string
    {
        return static::to(static::getRouter()->logout, $params, $absolute);
    }

    /**
     * Вернуть роутер для генерации адреса.
     * @return RouteManager
     */
    protected static function getRouter(): RouteManager
    {
        return Twin::app()->route;
    }
}
