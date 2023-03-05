<?php

namespace twin\controller;

use twin\common\Exception;
use twin\route\Route;
use ReflectionClass;
use ReflectionMethod;

abstract class Controller
{
    /**
     * Постфикс названия контроллера.
     */
    const CONTROLLER_POSTFIX = 'Controller';

    /**
     * Текущий роут.
     * @var Route
     */
    public $route;

    /**
     * Экземпляр запущенного контроллера.
     * @var static
     */
    public static $instance;

    protected function __construct() {}

    private function __clone() {}

    private function __wakeup() {}

    /**
     * Сценарий, выполняющийся до инициализации контроллера.
     * @return void
     */
    protected function init() {}

    /**
     * Инстанцировать контроллер.
     * @param string $namespace - неймспейс контроллера
     * @param string $controller - название контроллера
     * @return static
     * @throws Exception
     */
    protected static function getController(string $namespace, string $controller): self
    {
        $controller = static::getControllerName($controller);
        $controllerName = "$namespace\\$controller";

        if (!class_exists($controllerName)) {
            throw new Exception(404);
        }

        if (!is_subclass_of($controllerName, static::class)) {
            throw new Exception(500, "$controllerName must extends " . static::class);
        }

        return new $controllerName;
    }

    /**
     * Преобразовать название контроллера в роуте в название класса.
     * @param string $name - some-name
     * @return string - SomeNameController
     */
    protected static function getControllerName(string $name): string
    {
        $parts = explode('-', $name);

        $parts = array_map(function ($part) {
            return ucfirst($part);
        }, $parts);

        return implode('', $parts) . static::CONTROLLER_POSTFIX;
    }

    /**
     * Преобразовать название действия в роуте в название метода.
     * @param string $name - some-name
     * @return string - someName
     */
    protected static function getActionName(string $name): string
    {
        $parts = explode('-', $name);

        $parts = array_map(function ($part) {
            return ucfirst($part);
        }, $parts);

        $name = implode('', $parts);
        return lcfirst($name);
    }

    /**
     * Проверка на наличие указанного действия в текущем контроллере.
     * @param string $action - название действия
     * @return bool
     */
    protected function actionExists(string $action): bool
    {
        $actions = $this->getActions();
        return in_array($action, $actions);
    }

    /**
     * Названия действий текущего контроллера.
     * @return array
     */
    protected function getActions(): array
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $result = [];

        foreach ($methods as $method) {
            if ($method->isStatic()) continue;
            $result[] = $method->name;
        }

        return $result;
    }

    /**
     * Сценарий, выполняющийся до вызова действия.
     * @param string $action - название действия
     * @return void
     */
    protected function beforeAction(string $action) {}

    /**
     * Вызов действия.
     * @param string $action - название действия
     * @param array $params - параметры
     * @return mixed
     */
    abstract protected function callAction(string $action, array $params);
}
