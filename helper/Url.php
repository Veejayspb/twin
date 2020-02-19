<?php

namespace twin\helper;

use twin\controller\Controller;
use twin\route\Route;
use twin\Twin;

class Url
{
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
        $url = Twin::app()->route->createUrl($r);
        if ($absolute) {
            $url = Request::$scheme . '://' . Request::$host . $url;
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
        $url = Twin::app()->route->createUrl($r);
        if ($absolute) {
            $url = Request::$scheme . '://' . Request::$host . $url;
        }
        return $url;
    }

    /**
     * Адрес главной страницы.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function main(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->route->main, $params, $absolute);
    }

    /**
     * Адрес страницы login.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function login(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->route->login, $params, $absolute);
    }

    /**
     * Адрес страницы logout.
     * @param array $params - параметры
     * @param bool $absolute - абсолютный адрес
     * @return string
     */
    public static function logout(array $params = [], bool $absolute = false): string
    {
        return static::to(Twin::app()->route->logout, $params, $absolute);
    }
}
