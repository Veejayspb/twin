<?php

namespace twin\route;

use twin\common\Component;
use twin\common\Exception;
use twin\Twin;

class RouteManager extends Component
{
    /**
     * Роут главной страницы.
     * @var string
     */
    public $main = 'site/index';

    /**
     * Роут страницы login.
     * @var string
     */
    public $login = 'auth/login';

    /**
     * Роут страницы logout.
     * @var string
     */
    public $logout = 'auth/logout';

    /**
     * Роут страницы ошибки.
     * @var string
     */
    public $error = 'site/error';

    /**
     * Неймспейсы контроллеров.
     * @var array
     */
    protected $namespaces = [];

    /**
     * Правила роутинга.
     * @var array
     */
    protected $rules = [];

    /**
     * Вернуть неймспейс контроллеров указанного модуля.
     * @param string|null $module - название модуля
     * @return string
     * @throws Exception
     */
    public function getNamespace(string $module = null): string
    {
        $module = $module ?: '';
        if (!array_key_exists($module, $this->namespaces)) {
            throw new Exception(500, "Module not found: $module");
        }
        return $this->namespaces[$module];
    }

    /**
     * Разобрать адрес.
     * @param string $url - адрес
     * @return Route|bool - FALSE в случае ошибки
     * @throws Exception
     */
    public function parseUrl(string $url)
    {
        foreach ($this->rules as $pattern => $route) {
            $class = $this->getRuleClass($route);
            $rule = Twin::object($class, compact('pattern', 'route')); /* @var RuleInterface $rule */
            $route = $rule->parseUrl($url);
            if ($route) return $route;
        }
        return false;
    }

    /**
     * Создать адрес.
     * @param Route $r - роут
     * @return string|bool
     */
    public function createUrl(Route $r)
    {
        foreach ($this->rules as $pattern => $route) {
            if (!$r->compare($route)) continue;
            $class = $this->getRuleClass($route);
            $rule = Twin::object($class, compact('pattern', 'route')); /* @var RuleInterface $rule */
            $url = $rule->createUrl($r);
            if ($url !== false) return $url;
        }
        return false;
    }

    /**
     * Определить название класса для обработки правила.
     * Если передано название класса, то этот класс и обрабатывает правило.
     * Если передан строковый роут, то обработкой занимается стандартный класс Rule.
     * @param string $route - строковый роут или название класса
     * @return string
     * @throws Exception
     * @see \twin\route\Rule
     */
    private function getRuleClass(string $route): string
    {
        if (class_exists($route)) {
            if (!is_subclass_of($route, RuleInterface::class)) {
                throw new Exception(500, 'Rule object: ' . get_class($route) . ' must extends ' . RuleInterface::class);
            }
            return $route;
        } else {
            return Rule::class;
        }
    }
}
