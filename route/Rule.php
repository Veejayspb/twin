<?php

namespace twin\route;

use twin\helper\Address;

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
        $url = $address->build()->path()->get();

        $placeholders = $this->extractPlaceholders($url);
        if ($placeholders === false) return false;

        $strRoute = $this->fillRoute($placeholders); // Строковый роут вида: module/controller/action или controller/action
        if (!preg_match('/^([a-z]+\/)?[a-z]+\/[a-z]+$/', $strRoute)) return false;

        $route = new Route;
        $route->setRoute($strRoute);
        $route->params = $address->params;
        $route->setProperties($placeholders);
        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrl(Route $route)
    {
        $reserved = $route->getReservedParams();
        $strRoute = $this->fillRoute($reserved);
        if ($strRoute != $route->getRoute()) return false;

        $pattern = $this->fillPattern($reserved + $route->params);
        if (preg_match('/<.*?>/', $pattern)) return false; // Если в паттерне еще остались плейсхолдеры
        return $pattern;
    }

    /**
     * Заполнить текстовый роут параметрами.
     * @param array $params - параметры: ключ => значение
     * @return string
     */
    private function fillRoute(array $params): string
    {
        $route = $this->route;
        foreach ($params as $name => $value) {
            $route = str_replace("<$name>", $value, $route);
        }
        return $route;
    }

    /**
     * Заполнить паттерн параметрами.
     * @param array $params - параметры: ключ => значение
     * @return string
     */
    private function fillPattern(array $params): string
    {
        $pattern = $this->pattern;
        foreach ($params as $name => $value) {
            $pattern = preg_replace("/<$name(:.+?)?>/", $value, $pattern);
        }
        return $pattern;
    }

    /**
     * Извлечь значения плейсхолдеров из адреса, согласно паттерну.
     * @param string $url - адрес
     * @return array|bool - FALSE, если адрес не соответствует паттерну.
     */
    private function extractPlaceholders(string $url)
    {
        $pattern = str_replace('/', '\/', $this->pattern);

        // Создать регулярное выражение с именованными параметрами.
        $pattern = preg_replace_callback('/<(.+?)(:(.+?))?\>/', function ($m) {
            $range = isset($m[3]) ? $m[3] : '.+?';
            $name = $m[1];
            return "(?P<$name>$range)";
        }, $pattern);

        if (!preg_match_all("/^$pattern$/", $url, $m)) return false;

        $result = [];
        foreach ($m as $key => $value) {
            if (is_int($key)) continue;
            $result[$key] = $value[0];
        }
        return $result;
    }
}
