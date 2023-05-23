<?php

namespace twin\controller;

use ReflectionMethod;
use twin\common\Exception;
use twin\helper\Header;
use twin\response\ResponseHtml;
use twin\route\Route;
use twin\Twin;
use twin\view\View;

abstract class WebController extends Controller
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        Twin::app()->setComponent('response', new ResponseHtml);
    }

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

        $action = static::getActionName($route->action);

        if (!$controller->actionExists($action)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$controller->access($action)) {
            throw new Exception(403);
        }

        $controller->beforeAction($action);
        $data = $controller->callAction($action, $route->params);

        echo Twin::app()->response->run($data);
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

        return call_user_func_array([$this, $action], $result);
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
     * @return string
     */
    protected function render(string $route, array $data = []): string
    {
        $view = $this->getView();
        return $view->renderLayout($route, $data);
    }

    /**
     * Перенаправление.
     * @param string $url - адрес
     * @return void
     */
    protected function redirect(string $url)
    {
        Header::instance()->add('Location', $url);
        exit;
    }

    /**
     * Обновление.
     * @param int $delay - задержка перед перезагрузкой
     * @return void
     */
    protected function refresh(int $delay = 0)
    {
        Header::instance()->add('Refresh', $delay);
        exit;
    }

    /**
     * View компонент.
     * @return View
     */
    protected function getView(): View
    {
        return Twin::app()->getComponent(View::class);
    }
}
