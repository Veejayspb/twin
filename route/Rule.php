<?php

namespace core\route;

use core\helper\Address;

class Rule implements RuleInterface
{
    /**
     * Паттерн.
     * @var string
     */
    public $pattern;

    /**
     * Текстовый роут.
     * @var string
     */
    public $route;

    /**
     * {@inheritdoc}
     */
    public function parseUrl(string $url)
    {
        // Убрать из адреса GET-параметры.
        $address = new Address($url);
        $url = $address->getUrl(false);

        // Подготовить паттерн.
        $pattern = str_replace('/', '\/', $this->pattern);
        preg_match_all('/<([a-z]+)(?::(.+?))?>/', $this->pattern, $matches);
        foreach ($matches[0] as $i => $str) {
            $replacement = $matches[2][$i] ?: '.+?';
            $pattern = str_replace($str, "($replacement)", $pattern);
        }
        $pattern = "/^$pattern$/";

        // Разбор адреса.
        if (!preg_match($pattern, $url, $values)) return false;
        unset($values[0]);

        // Формирование роута.
        $route = new Route;
        $route->params = $address->params;
        $route->setProperties(array_combine($matches[1], $values));
        $route->setRoute($this->route);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrl(Route $route)
    {
        // Зарезервированные параметры
        $url = $this->pattern;
        foreach (Route::$reserved as $param) {
            $url = preg_replace("/<$param(:.+?)?>/", $route->$param, $url);
        }

        // Параметры
        $params = $route->params;
        if (array_key_exists('#', $params)) {
            $anchor = $params['#'];
            unset($params['#']);
        }
        foreach ($params as $key => $value) {
            $url = preg_replace("/<$key(:.+?)?>/", $value, $url, -1, $count);
            if ($count) unset($params[$key]);
        }
        if (preg_match("/<[a-z]+(:.+?)?>/", $url)) return false; // Если были указаны не все параметры
        $address = new Address($url);
        $address->params = $params;
        if (isset($anchor)) {
            $address->anchor = $anchor;
        }
        return $address->getUrl(true, false, true);
    }
}
