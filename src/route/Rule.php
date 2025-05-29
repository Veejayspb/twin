<?php

namespace twin\route;

use twin\helper\Address;

class Rule implements RuleInterface
{
    /**
     * Паттерн.
     * @var string
     */
    public string $pattern;

    /**
     * Текстовый роут.
     * @var string
     */
    public string $route;

    /**
     * {@inheritdoc}
     */
    public function parseUrl(string $url): Route|bool
    {
        // Убрать из адреса GET-параметры.
        $address = new Address($url);
        $placeholders = $this->extractPlaceholders($address->path);

        if ($placeholders === false) {
            return false;
        }

        $strRoute = $this->fillRoute($placeholders); // Строковый роут вида: module/controller/action или controller/action

        if (!preg_match(Route::ROUTE_PATTERN, $strRoute)) {
            return false;
        }

        $route = new Route;
        $route->parse($strRoute);
        $route->setProperties($placeholders);
        $route->params += $address->params;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrl(Route $route): bool|string
    {
        $address = new Address;
        $url = $this->pattern;

        // Проверка на соответствие строковому роуту
        $reserved = $route->getReservedParams();
        $strRoute = $this->fillRoute($reserved);

        if ($strRoute != $route->stringify()) {
            return false;
        }

        // Замена зарезервированных параметров
        foreach ($reserved as $name => $value) {
            $this->replacePlaceholder($url, $name, $value);
        }

        // Замена параметров
        $params = [];

        foreach ($route->params as $name => $value) {
            $replacement = $this->replacePlaceholder($url, $name, $value);

            if (!$replacement) {
                $params[$name] = $value;
            }
        }

        if ($this->hasPlaceholders($url)) return false;

        $address->path = $url;
        $address->params = $params;

        return $address->getUrl(true, false, true);
    }

    /**
     * Заменить плейсхолдер на значение с проверкой (если указан паттерн).
     * @param string $str - исходная строка с плейсхолдерами
     * @param string $name - название плейсхолдера
     * @param string|null $value - значение, на которое заменить
     * NULL нужно для того, чтобы можно было убирать параметры из адреса: Url::current(['param' => null])
     * @return bool
     */
    private function replacePlaceholder(string &$str, string $name, ?string $value): bool
    {
        $str = preg_replace_callback("/<$name(:(.+?))?>/", function ($m) use ($value) {

            if (!array_key_exists(2, $m) || preg_match('/^' . $m[2] . '$/', $value)) {
                return $value;
            } else {
                return $m[0];
            }

        }, $str, -1, $count);

        return 0 < $count;
    }

    /**
     * Проверяет наличие в строке плейсхолдеров.
     * @param string $str - строка, в которой необходимо искать плейсхолдеры
     * @return bool
     */
    private function hasPlaceholders(string $str): bool
    {
        return preg_match('/<.+?>/', $str);
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
     * Извлечь значения плейсхолдеров из адреса, согласно паттерну.
     * @param string $url - адрес
     * @return array|bool - FALSE, если адрес не соответствует паттерну.
     */
    private function extractPlaceholders(string $url): bool|array
    {
        $pattern = str_replace('/', '\/', $this->pattern);

        // Создать регулярное выражение с именованными параметрами.
        $pattern = preg_replace_callback('/<(.+?)(:(.+?))?>/', function ($m) {
            $range = $m[3] ?? '.+?';
            $name = $m[1];
            return "(?P<$name>$range)";
        }, $pattern);

        if (!preg_match_all("/^$pattern$/", $url, $m)) {
            return false;
        }

        $result = [];

        foreach ($m as $key => $value) {
            if (is_int($key)) continue;
            $result[$key] = $value[0];
        }

        return $result;
    }
}
