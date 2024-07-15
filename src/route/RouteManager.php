<?php

namespace twin\route;

use twin\common\Component;
use twin\common\Exception;
use twin\controller\Controller;
use twin\helper\Request;
use twin\helper\StringHelper;

class RouteManager extends Component
{
    /**
     * Роут главной страницы.
     * @var string
     */
    public $home = '/main/index';

    /**
     * Роут страницы login.
     * @var string
     */
    public $login = '/auth/login';

    /**
     * Роут страницы logout.
     * @var string
     */
    public $logout = '/auth/logout';

    /**
     * Роут страницы ошибки.
     * @var string
     */
    public $error = '/main/error';

    /**
     * Неймспейсы контроллеров.
     * @var array
     */
    public $namespaces = [];

    /**
     * Правила роутинга.
     * @var array
     */
    public $rules = [];

    /**
     * Адрес домена.
     * @var string - https://domain.ru
     */
    public $domain = '';

    /**
     * Постфикс названия контроллера.
     * @var string
     */
    public $controllerPostfix = 'Controller';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['home', 'login', 'logout', 'error', 'namespaces', 'rules'];

    /**
     * Вернуть неймспейс контроллеров указанного модуля.
     * @param string|null $module - название модуля
     * @return string|null
     */
    public function getNamespace(?string $module): ?string
    {
        return $this->namespaces[$module] ?? null;
    }

    /**
     * Вернуть текущий роут.
     * @return Route|null
     */
    public function getCurrentRoute(): ?Route
    {
        if (!is_string(Request::$url)) {
            return null;
        }

        return $this->parseUrl(Request::$url);
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
     * @param bool $absolute - абсолютный адрес
     * @return string|bool
     */
    public function createUrl(Route $route, bool $absolute = false)
    {
        $url = $this->compareRoutes(function (RuleInterface $rule) use ($route) {
            return $rule->createUrl($route);
        });

        if (!$absolute) {
            return $url;
        }

        if ($url) {
            return $this->domain . $url;
        }

        return false;
    }

    /**
     * Вернуть инстанс контроллера.
     * @param string|null $module
     * @param string $controller
     * @return Controller|null
     */
    public function getController(?string $module, string $controller): ?Controller
    {
        $namespace = $this->getNamespace($module);

        if (!$namespace) {
            return null;
        }

        $controllerName = $this->getControllerName($controller);

        if (!$controllerName) {
            return null;
        }

        $className = $namespace . '\\' . $controllerName;

        if (!is_subclass_of($className, Controller::class)) {
            return null;
        }

        return new $className;
    }

    /**
     * Преобразовать название контроллера в роуте в название класса.
     * @param string $name - some-name
     * @return string - SomeNameController
     */
    protected function getControllerName(string $name): string
    {
        return StringHelper::kabobToCamel($name) . $this->controllerPostfix;
    }

    /**
     * Определить название класса для обработки правила.
     * Если передано название класса, то этот класс и обрабатывает правило.
     * Если передан строковый роут, то обработкой занимается стандартный класс Rule.
     * @param string $route - строковый роут или название класса
     * @return string|null
     */
    protected function getRuleClass(string $route): ?string
    {
        if (!class_exists($route)) {
            return Rule::class;
        }

        if (is_subclass_of($route, RuleInterface::class)) {
            return $route;
        }

        return null;
    }

    /**
     * Выбрать правило, удовлетворяющее коллбэк-функции.
     * @param callable $func - функция, проверяющая соответствие роута
     * @return Route|bool - FALSE, если ни одно правило не соответствует
     * @throws Exception
     */
    private function compareRoutes(callable $func)
    {
        foreach ($this->rules as $pattern => $route) {
            $className = $this->getRuleClass($route);

            if ($className === null) {
                $message = 'Rule object: ' . get_class($route) . ' must be implemented ' . RuleInterface::class;
                throw new Exception(500, $message);
            }

            $rule = new $className; /* @var RuleInterface $rule */
            $rule->pattern = $pattern;
            $rule->route = $route;
            $result = $func($rule);

            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }
}
