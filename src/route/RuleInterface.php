<?php

namespace twin\route;

interface RuleInterface
{
    /**
     * Разбор адреса.
     * @param string $url - адрес
     * @return Route|null - NULL, если адрес не соответствует текущему правилу
     */
    public function parseUrl(string $url): ?Route;

    /**
     * Создание адреса.
     * @param Route $route - объект роута
     * @return string|null - NULL, если не удалось создать адрес
     */
    public function createUrl(Route $route): ?string;
}
