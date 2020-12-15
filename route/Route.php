<?php

namespace twin\route;

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
     * @var string
     */
    public $module = '';

    /**
     * Контроллер.
     * @var string
     */
    public $controller = self::CONTROLLER;

    /**
     * Действие.
     * @var string
     */
    public $action = self::ACTION;

    /**
     * Параметры.
     * @var array
     */
    public $params = [];

    /**
     * Зарезервированные названия параметров.
     * @var array
     */
    private $reserved = ['module', 'controller', 'action'];

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
     * Заполнить свойства объекта.
     * Сначала заполняются module, controller, action.
     * Остальные значения попадают в params.
     * @param array $properties - данные
     * @return self
     */
    public function setProperties(array $properties): self
    {
        foreach ($properties as $name => $value) {
            if (in_array($name, $this->reserved)) {
                $this->setProperty($name, $value);
            } else {
                $this->params[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Вернуть значения зарезервированных параметров: module, controller, action.
     * @return array
     */
    public function getReservedParams(): array
    {
        $result = [];
        foreach ($this->reserved as $name) {
            if (empty($this->$name)) continue;
            $result[$name] = $this->$name;
        }
        return $result;
    }

    /**
     * Вернуть текстовый роут.
     * @return string - module/controller/action
     */
    public function getRoute(): string
    {
        $reservedParams = $this->getReservedParams();
        return implode('/', $reservedParams);
    }

    /**
     * Установить значения зарезервированных параметров на основе текстового роута.
     * @param string $route - текстовый роут вида:
     * module/controller/action
     * controller/action
     * action
     * @return void
     */
    public function setRoute(string $route)
    {
        $parts = explode('/', $route);
        $part = array_pop($parts);
        if ($part !== null) $this->setAction($part);
        $part = array_pop($parts);
        if ($part !== null) $this->setController($part);
        $part = array_pop($parts);
        if ($part !== null) $this->setModule($part);
    }

    /**
     * Установка модуля.
     * @param mixed $value - значение
     * @return void
     */
    private function setModule($value)
    {
        if (empty($value)) {
            $this->module = '';
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
}
