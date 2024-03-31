<?php

namespace twin\controller;

use ReflectionMethod;
use twin\Twin;
use twin\view\View;

abstract class WebController extends Controller
{
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
     * View компонент.
     * @return View|null
     */
    protected function getView(): ?View
    {
        return Twin::app()->findComponent(View::class);
    }
}
