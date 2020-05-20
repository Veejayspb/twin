<?php

namespace twin\controller;

use twin\common\Exception;
use twin\route\Route;
use twin\view\View;
use ReflectionMethod;

abstract class WebController extends Controller
{
    /**
     * Объект для работы с видами.
     * @var View
     */
    protected $view;

    /**
     * Вызвать указанные контроллер/действие.
     * @param string $namespace - неймспейс контроллера
     * @param Route $route - роут
     * @param View $view - объект для работы с видами
     * @return void
     * @throws Exception
     */
    public static function run(string $namespace, Route $route, View $view)
    {
        if (self::class != get_called_class()) {
            throw new Exception(500, 'Denied to run controller not from class: ' . self::class);
        }
        $controller = self::$instance = static::getController($namespace, $route->controller);
        $controller->view = $view;
        $controller->route = $route;
        $controller->init();

        if (!$controller->actionExists($route->action)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$controller->access($route->action)) {
            throw new Exception(403);
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
        foreach ($parameters as $parameter) {
            if (array_key_exists($parameter->name, $params)) {
                $result[$parameter->name] = $params[$parameter->name];
            } elseif (!$parameter->isOptional()) {
                $result[$parameter->name] = null;
            }
        }
        call_user_func_array([$this, $action], $result);
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
     * Рендер вида с шаблоном.
     * @param string $route - строковой роут
     * @param array $data - данные
     * @return void
     */
    protected function render(string $route, array $data = [])
    {
        echo $this->view->renderLayout($route, $data);
    }

    /**
     * Перенаправление.
     * @param string $url - адрес
     * @return void
     */
    protected function redirect(string $url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Обновление.
     * @return void
     */
    protected function refresh()
    {
        header('Refresh:0');
        exit;
    }
}
