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
    public $home = 'site/index';

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
     * @param string $module - название модуля
     * @return string
     * @throws Exception
     */
    public function getNamespace(string $module = ''): string
    {
        if (!array_key_exists($module, $this->namespaces)) {
            throw new Exception(500, "Module not found: $module");
        }
        return $this->namespaces[$module];
    }

    /**
     * Разобрать адрес.
     * @param string $url - адрес
     * @return Route|bool - FALSE в случае ошибки
     */
    public function parseUrl(string $url)
    {
        return $this->compareRoutes(function (RuleInterface $rule) use ($url) {
            return $rule->parseUrl($url);
        });
    }

    /**
     * Создать адрес.
     * @param Route $route - роут
     * @return string|bool
     */
    public function createUrl(Route $route)
    {
        return $this->compareRoutes(function (RuleInterface $rule) use ($route) {
            return $rule->createUrl($route);
        });
    }

    /**
     * Определить название класса для обработки правила.
     * Если передано название класса, то этот класс и обрабатывает правило.
     * Если передан строковый роут, то обработкой занимается стандартный класс Rule.
     * @param string $route - строковый роут или название класса
     * @return string
     * @throws Exception
     */
    protected function getRuleClass(string $route): string
    {
        if (class_exists($route)) {
            if (!is_subclass_of($route, RuleInterface::class)) {
                throw new Exception(500, 'Rule object: ' . get_class($route) . ' must be implemented ' . RuleInterface::class);
            }
            return $route;
        } else {
            return Rule::class;
        }
    }

    /**
     * Выбрать правило, удовлетворяющее коллбэк-функции.
     * @param callable $func - функция, проверяющая соответствие роута
     * @return string|Route|bool - FALSE, если ни одно правило не соответствует
     */
    private function compareRoutes(callable $func)
    {
        foreach ($this->rules as $pattern => $route) {
            $class = $this->getRuleClass($route);
            $rule = Twin::createObject($class, compact('pattern', 'route')); /* @var RuleInterface $rule */
            $result = $func($rule);
            if ($result !== false) return $result;
        }
        return false;
    }
}
