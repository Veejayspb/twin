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
    const POSTFIX = 'Controller';

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
        $controller.= static::POSTFIX;
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
            $result[] = mb_strtolower($method->name);
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
     * @return void
     */
    abstract protected function callAction(string $action, array $params);
}
