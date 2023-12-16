<?php

namespace twin\controller;

use ReflectionMethod;
use twin\helper\Header;
use twin\response\ResponseHtml;
use twin\Twin;
use twin\view\View;

abstract class WebController extends Controller
{
    public function __construct()
    {
        Twin::app()->setComponent('response', new ResponseHtml);
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
        (new Header)->add('Location', $url);
        exit;
    }

    /**
     * Обновление.
     * @param int $delay - задержка перед перезагрузкой
     * @return void
     */
    protected function refresh(int $delay = 0)
    {
        (new Header)->add('Refresh', $delay);
        exit;
    }

    /**
     * View компонент.
     * @return View|null
     */
    protected function getView(): ?View
    {
        return Twin::app()->findComponent(View::class);
    }
}
