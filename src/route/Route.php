<?php

namespace twin\route;

class Route
{
    const DEFAULT_MODULE = '';
    const DEFAULT_CONTROLLER = 'main';
    const DEFAULT_ACTION = 'index';

    const RESERVED = [
        'module',
        'controller',
        'action',
    ];

    /**
     * Паттерн зарезервированных названий параметров.
     */
    const PATTERN = '(?!\-)[a-z-]+(?<=[^-])';

    /**
     * Паттерн текстового роута.
     */
    const ROUTE_PATTERN = '/^(' . self::PATTERN . '\/)?' . self::PATTERN . '\/' . self::PATTERN . '$/';

    /**
     * Модуль.
     * @var string
     */
    public string $module = self::DEFAULT_MODULE;

    /**
     * Контроллер.
     * @var string
     */
    public string $controller = self::DEFAULT_CONTROLLER;

    /**
     * Действие.
     * @var string
     */
    public string $action = self::DEFAULT_ACTION;

    /**
     * Параметры.
     * @var array
     */
    public array $params = [];

    /**
     * @param string|null $module
     * @param string|null $controller
     * @param string|null $action
     * @param array $params
     */
    public function __construct(
        ?string $module = null,
        ?string $controller = null,
        ?string $action = null,
        array $params = []
    ) {
        $this->module = $module ?: static::DEFAULT_MODULE;
        $this->controller = $controller ?: static::DEFAULT_CONTROLLER;
        $this->action = $action ?: static::DEFAULT_ACTION;
        $this->params = $params;
    }

    /**
     * Заполнить свойства объекта.
     * Сначала заполняются: module, controller, action.
     * Остальные значения попадают в params.
     * @param array $properties - данные
     * @return void
     */
    public function setProperties(array $properties): void
    {
        $this->params = [];

        foreach ($properties as $name => $value) {
            if ($this->isReserved($name)) {
                $this->$name = $value;
                unset($properties[$name]);
            }
        }

        $this->params = $properties;
    }

    /**
     * Вернуть значения зарезервированных параметров: module, controller, action.
     * @return array
     */
    public function getReservedParams(): array
    {
        $result = [];

        foreach (static::RESERVED as $reserved) {
            if (empty($this->$reserved)) {
                continue;
            }

            $result[$reserved] = $this->$reserved;
        }

        return $result;
    }

    /**
     * Вернуть текстовый роут.
     * @return string - module/controller/action
     */
    public function stringify(): string
    {
        $reservedParams = $this->getReservedParams();
        return implode('/', $reservedParams);
    }

    /**
     * Разобрать текстовый роут и установить значения зарезервированных параметров.
     * @param string $route - текстовый роут вида:
     * module/controller/action
     * controller/action
     * action
     * @return void
     */
    public function parse(string $route): void
    {
        $parts = explode('/', $route);
        $reservedParams = array_reverse(static::RESERVED); // Разбор роута начинаем с конца (с действия)

        foreach ($reservedParams as $name) {
            $part = array_pop($parts);

            if ($part !== null) {
                $this->$name = $part;
            }
        }
    }

    /**
     * Является ли указанный параметр зарезервированным.
     * @param string $name
     * @return bool
     */
    protected function isReserved(string $name): bool
    {
        return in_array($name, static::RESERVED);
    }
}
