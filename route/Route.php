<?php

namespace core\route;

/**
 * Class Route
 * @package core\route
 *
 * @property string|null $module
 * @property string $controller
 * @property string $action
 * @property array $params
 */
final class Route
{
    /**
     * Паттерн зарезервированных названий параметров.
     * @see $reserved
     */
    const PATTERN = '/^[a-z]+$/';

    /**
     * Контроллер по-умолчанию.
     */
    const CONTROLLER = 'site';

    /**
     * Действие по-умолчанию.
     */
    const ACTION = 'index';

    /**
     * Модуль.
     * @var string|null
     */
    private $module;

    /**
     * Контроллер.
     * @var string
     */
    private $controller = self::CONTROLLER;

    /**
     * Действие.
     * @var string
     */
    private $action = self::ACTION;

    /**
     * Параметры.
     * @var array
     */
    private $params = [];

    /**
     * Зарезервированные названия параметров.
     * @var array
     */
    public static $reserved = ['module', 'controller', 'action'];

    /**
     * @param string|null $module - модуль
     * @param string $controller - контроллер
     * @param string $action - действие
     * @param array $params - параметры
     */
    public function __construct(string $module = null, string $controller = self::CONTROLLER, string $action = self::ACTION, array $params = [])
    {
        $this->setModule($module);
        $this->setController($controller);
        $this->setAction($action);
        $this->setParams($params);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->setProperty($name, $value);
    }

    /**
     * @param string $name
     * @return string|array|null
     */
    public function __get($name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }

    /**
     * Установка модуля.
     * @param mixed $value - значение
     * @return void
     */
    private function setModule($value)
    {
        if (empty($value)) {
            $this->module = null;
        } elseif (is_string($value) && preg_match(self::PATTERN, $value)) {
            $this->module = $value;
        }
    }

    /**
     * Установка контроллера.
     * @param mixed $value - значение
     * @return void
     */
    private function setController($value)
    {
        if (empty($value)) {
            $this->controller = self::CONTROLLER;
        } elseif (is_string($value) && preg_match(self::PATTERN, $value)) {
            $this->controller = $value;
        }
    }

    /**
     * Установка действия.
     * @param mixed $value - значение
     * @return void
     */
    private function setAction($value)
    {
        if (empty($value)) {
            $this->action = self::ACTION;
        } elseif (is_string($value) && preg_match(self::PATTERN, $value)) {
            $this->action = $value;
        }
    }

    /**
     * Установка параметров.
     * @param mixed $value - параметры
     * @return void
     */
    private function setParams($value)
    {
        if (is_array($value)) {
            ksort($value);
            $this->params = $value;
        }
    }

    /**
     * Установка свойства объекта.
     * @param string $name - module, controller, action
     * @param mixed $value - значение
     * @return void
     */
    private function setProperty(string $name, $value)
    {
        switch ($name) {
            case 'module':
                $this->setModule($value);
                break;
            case 'controller':
                $this->setController($value);
                break;
            case 'action':
                $this->setAction($value);
                break;
            case 'params':
                $this->setParams($value);
                break;
        }
    }

    /**
     * Заполнить свойства объекта.
     * Сначала заполняются module, controller, action.
     * Остальные значения попадают в params.
     * @param array $properties - данные
     * @return self
     */
    public function setProperties(array $properties): Route
    {
        foreach ($properties as $name => $value) {
            if (in_array($name, self::$reserved)) {
                $this->setProperty($name, $value);
            } else {
                $this->params[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Вернуть текстовый роут.
     * @return string - module/controller/action
     */
    public function getRoute(): string
    {
        $parts = [];
        foreach (self::$reserved as $param) {
            if ($this->$param !== null) {
                $parts[] = $this->$param;
            }
        }
        return implode('/', $parts);
    }

    /**
     * Установить значения зарезервированных параметров на основе текстового роута.
     * @param string $route - текстовый роут вида: module/controller/action
     * @return void
     */
    public function setRoute(string $route)
    {
        $parts = explode('/', $route);
        $params = array_reverse(self::$reserved);
        foreach ($params as $param) {
            if ($part = array_pop($parts)) {
                if ($part === null) break;
                $this->setProperty($param, $part);
            }
        }
    }

    /**
     * Сравнить строковый роут и текущий.
     * @param string $route - строковый роут для сравнения
     * @return bool
     */
    public function compare(string $route): bool
    {
        $route = str_replace('<action>', $this->action, $route);
        $route = str_replace('<controller>', $this->controller, $route);
        $route = str_replace('<module>', $this->module, $route);
        return $route == $this->getRoute();
    }
}
