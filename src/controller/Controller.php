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
    protected $actionPrefix = 'action';

    /**
     * Запуск действия
     * @param string $action
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function runAction(string $action, array $params)
    {
        $actionName = $this->getActionName($action);

        if (!$this->actionExists($actionName)) {
            throw new Exception(404);
        }

        // Права доступа.
        if (!$this->access($actionName)) {
            throw new Exception(403);
        }

        $data = $this->action($actionName, $params);

        echo Twin::app()->response->run($data);
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
    public function actionExists(string $action): bool
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
            if ($method->isStatic()) continue;
            $result[] = $method->name;
        }

        return $result;
    }

    /**
     * Прямой вызов действия.
     * @param string $action - название действия
     * @param array $params - параметры
     * @return mixed
     */
    abstract protected function action(string $action, array $params);
}
