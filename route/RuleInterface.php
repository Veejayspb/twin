<?php

namespace core\route;

interface RuleInterface
{
    /**
     * Разбор адреса.
     * @param string $url - адрес
     * @return Route|bool - FALSE, если адрес не соответствует
     */
    public function parseUrl(string $url);

    /**
     * Создание адреса.
     * @param Route $route - объект роута
     * @return string|bool - FALSE, если не удалось создать адрес
     */
    public function createUrl(Route $route);
}
