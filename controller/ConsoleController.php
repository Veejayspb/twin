<?php

namespace twin\controller;

use twin\common\Exception;
use twin\route\Route;
use ReflectionMethod;

abstract class ConsoleController extends Controller
{
    /**
     * Вызвать указанные контроллер/действие.
     * @param string $namespace - неймспейс контроллера
     * @param Route $route - роут
     * @return void
     * @throws Exception
     */
    public static function run(string $namespace, Route $route)
    {
        if (self::class != get_called_class()) {
            throw new Exception(500, 'Denied to run controller not from class: ' . self::class);
        }
        $controller = self::$instance = static::getController($namespace, $route->controller);
        $controller->route = $route;
        $controller->init();

        if (!$controller->actionExists($route->action)) {
            throw new Exception(404);
        }

        $controller->beforeAction($route->action);
        $controller->callAction($route->action, $route->params);
    }

    /**
     * {@inheritdoc}
     */
    protected function callAction(string $action, array $params)
    {
        $reflection = new ReflectionMethod($this, $action);
        $parameters = $reflection->getParameters();
        $result = [];
        foreach ($parameters as $i => $parameter) {
            if (array_key_exists($i, $params)) {
                $result[] = $params[$i];
            } elseif (!$parameter->isOptional()) {
                throw new Exception(404, 'Required attribute is not specified: ' . $parameter->name);
            }
        }
        call_user_func_array([$this, $action], $result);
    }
}
