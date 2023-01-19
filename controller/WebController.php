<?php

namespace twin\controller;

use ReflectionClass;
use ReflectionMethod;
use twin\common\Exception;
use twin\route\Route;
use twin\Twin;
use twin\view\View;

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

        $action = static::getActionName($route->action);

        if (!$controller->actionExists($action)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$controller->access($action)) {
            throw new Exception(403);
        }

        $controller->beforeAction($action);
        $controller->callAction($action, $route->params);
    }

    /**
     * Создать адрес.
     * @param string $action - название действия
     * @param array $params - параметры
     * @return string
     * @throws Exception
     */
    public static function createUrl(string $action, array $params = []): string
    {
        if (__CLASS__ == static::class) {
            throw new Exception(500, "Can't call method from " . __CLASS__);
        }

        $reflection = new ReflectionClass(static::class);
        $namespace = $reflection->getNamespaceName();
        $module = Twin::app()->route->getModule($namespace);
        if ($module === false) {
            throw new Exception(500, "Can't create url to " . static::class . ' controller');
        }

        $controller = preg_replace('/Controller$/', '', $reflection->getShortName());
        $controller = strtolower($controller);
        if (!Route::validParam($controller)) {
            throw new Exception(500, "Wrong controller name: $controller");
        }

        if (!Route::validParam($action)) {
            throw new Exception(500, "Wrong action name: $action");
        }

        $route = new Route($module, $controller, $action, $params);
        return Twin::app()->route->createUrl($route);
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
