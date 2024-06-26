<?php

namespace twin\controller;

use ReflectionClass;
use ReflectionMethod;
use twin\common\Exception;
use twin\route\Route;
use twin\Twin;

abstract class Controller
{
    /**
     * Постфикс названия контроллера.
     */
    const POSTFIX = 'Controller';

    /**
     * Вызвать указанные контроллер/действие.
     * @param string $namespace - неймспейс контроллера
     * @param Route $route - роут
     * @return void
     * @throws Exception
     */
    public static function run(string $namespace, Route $route)
    {
        $controller = static::getController($namespace, $route->controller);
        $action = static::getActionName($route->action);

        if (!$controller->actionExists($action)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$controller->access($action)) {
            throw new Exception(403);
        }

        $data = $controller->callAction($action, $route->params);

        echo Twin::app()->response->run($data);
    }

    /**
     * Разрешен ли доступ к действию.
     * @param string $action - название действия
     * @return bool
     */
    protected function access(string $action): bool
    {
        return true;
    }

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

        return implode('', $parts) . static::POSTFIX;
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
     * Вызов действия.
     * @param string $action - название действия
     * @param array $params - параметры
     * @return mixed
     */
    abstract protected function callAction(string $action, array $params);
}
