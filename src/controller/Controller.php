<?php

namespace twin\controller;

use ReflectionClass;
use ReflectionMethod;
use twin\common\Exception;
use twin\helper\StringHelper;
use twin\Twin;

abstract class Controller
{
    /**
     * Префикс названия действия.
     * @var string
     */
    protected string $actionPrefix = 'action';

    /**
     * Запуск действия
     * @param string $action
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function runAction(string $action, array $params): void
    {
        $actionName = $this->getActionName($action);

        if (!$this->hasAction($actionName)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$this->access($action)) {
            throw new Exception(403);
        }

        $data = $this->action($actionName, $params);

        echo Twin::app()->di->get('response')->run($data);
    }

    /**
     * Разрешен ли доступ к действию.
     * @param string $action - название действия
     * @return bool
     */
    public function access(string $action): bool
    {
        return true;
    }

    /**
     * Преобразовать название действия в роуте в название метода.
     * @param string $name - some-name
     * @return string - someName
     */
    public function getActionName(string $name): string
    {
        return $this->actionPrefix . StringHelper::kabobToCamel($name);
    }

    /**
     * Проверка на наличие указанного действия в текущем контроллере.
     * @param string $action - название действия
     * @return bool
     */
    public function hasAction(string $action): bool
    {
        $actions = $this->getActions();
        return in_array($action, $actions);
    }

    /**
     * Названия действий текущего контроллера.
     * @return array
     */
    public function getActions(): array
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $result = [];

        foreach ($methods as $method) {
            if (
                $method->isStatic() ||
                $this->checkActionName($method->name)
            ) {
                continue;
            }

            $result[] = $method->name;
        }

        return $result;
    }

    /**
     * Проверка корректности названия действия.
     * @param string $actionName
     * @return bool
     */
    protected function checkActionName(string $actionName): bool
    {
        $prefixLength = strlen($this->actionPrefix);
        return substr($actionName, 0, $prefixLength) != $this->actionPrefix;
    }

    /**
     * Прямой вызов действия.
     * @param string $action - название действия
     * @param array $params - параметры
     * @return mixed
     */
    protected function action(string $action, array $params): mixed
    {
        $reflection = new ReflectionMethod($this, $action);
        $parameters = $reflection->getParameters();
        $result = [];

        foreach ($parameters as $i => $parameter) {
            if (array_key_exists($parameter->name, $params)) {
                $result[] = $params[$parameter->name];
            } elseif (array_key_exists($i, $params)) {
                $result[] = $params[$i];
            } elseif (!$parameter->isOptional()) {
                $result[] = null;
            }
        }

        return call_user_func_array([$this, $action], $result);
    }
}
