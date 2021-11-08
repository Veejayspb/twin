<?php

namespace twin\route;

final class Route
{
    /**
     * Паттерн зарезервированных названий параметров.
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
     * Названия зарезервированных параметров.
     */
    const RESERVED_PARAMS = ['module', 'controller', 'action'];

    /**
     * @param string|null $module - модуль
     * @param string $controller - контроллер
     * @param string $action - действие
     * @param array $params - параметры
     */
    public function __construct(
        string $module = null,
        string $controller = self::CONTROLLER,
        string $action = self::ACTION,
        array $params = []
    ) {
        foreach (static::RESERVED_PARAMS as $name) {
            $this->setReservedParam($name, $$name);
        }
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
            if (in_array($name, static::RESERVED_PARAMS)) {
                $this->setReservedParam($name, $value);
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
        foreach (static::RESERVED_PARAMS as $name) {
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
        $reservedParams = array_reverse(static::RESERVED_PARAMS); // Потому что разбор роута начинаем с действия (action)

        foreach ($reservedParams as $name) {
            $part = array_pop($parts);
            if ($part !== null) $this->setReservedParam($name, $part);
        }
    }

    /**
     * Установка значения зарезервированного параметра.
     * @param string $type - название параметра: module, controller, action
     * @param string|null $value - значение
     * @return void
     */
    private function setReservedParam(string $type, $value)
    {
        if (!in_array($type, static::RESERVED_PARAMS)) return;

        if (empty($value)) {
            $this->$type = self::CONTROLLER;
        } elseif (is_string($value) && preg_match(self::PATTERN, $value)) {
            $this->$type = $value;
        }
    }

    /**
     * Установка значений незарезервированных параметров.
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
}
