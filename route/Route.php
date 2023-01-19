<?php

namespace twin\route;

class Route
{
    /**
     * Паттерн зарезервированных названий параметров.
     */
    const PATTERN = '[a-z\-]+';

    /**
     * Паттерн текстового роута.
     */
    const ROUTE_PATTERN = '/^(' . self::PATTERN . '\/)?' . self::PATTERN . '\/' . self::PATTERN . '$/';

    /**
     * Контроллер по-умолчанию.
     */
    const CONTROLLER = 'site';

    /**
     * Действие по-умолчанию.
     */
    const ACTION = 'index';

    /**
     * Названия зарезервированных названий параметров.
     */
    const RESERVED_PARAMS = ['module', 'controller', 'action'];

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
     * @param string|null $module - модуль
     * @param string $controller - контроллер
     * @param string $action - действие
     * @param array $params - параметры
     */
    public function __construct(
        string $module = '',
        string $controller = self::CONTROLLER,
        string $action = self::ACTION,
        array $params = []
    ) {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;

        ksort($params);
        $this->params = $params;
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
                $this->$name = $value;
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
        $reservedParams = array_reverse(static::RESERVED_PARAMS); // Потому что разбор роута начинаем с конца (action)

        foreach ($reservedParams as $name) {
            $part = array_pop($parts);
            if ($part !== null) {
                $this->$name = $part;
            }
        }
    }

    /**
     * Является ли зарезервированный параметр валидным.
     * @param string $value
     * @return bool
     */
    public static function validParam(string $value): bool
    {
        $pattern = '/^' . self::PATTERN . '$/';
        return preg_match($pattern, $value);
    }
}
