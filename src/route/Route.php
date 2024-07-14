<?php

namespace twin\route;

class Route
{
    /**
     * Названия и значения зарезервированных параметров.
     */
    const DEFAULT = [
        'module' => '',
        'controller' => 'site',
        'action' => 'index',
    ];

    const DEFAULT_MODULE = '';
    const DEFAULT_CONTROLLER = 'site';
    const DEFAULT_ACTION = 'index';

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
    public $module = self::DEFAULT_MODULE;

    /**
     * Контроллер.
     * @var string
     */
    public $controller = self::DEFAULT_CONTROLLER;

    /**
     * Действие.
     * @var string
     */
    public $action = self::DEFAULT_ACTION;

    /**
     * Параметры.
     * @var array
     */
    public $params = [];

    /**
     * @param string|null $module - модуль
     * @param string|null $controller - контроллер
     * @param string|null $action - действие
     * @param array $params - параметры
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
     * Сначала заполняются module, controller, action.
     * Остальные значения попадают в params.
     * @param array $properties - данные
     * @return void
     */
    public function setProperties(array $properties): void
    {
        $this->params = [];

        foreach ($properties as $name => $value) {
            if ($this->isReserved($name)) {
                $this->setReserved($name, $value);
                unset($properties[$name]);
            }
        }

        $this->setParams($properties);
    }

    /**
     * Вернуть значения зарезервированных параметров: module, controller, action.
     * @return array
     */
    public function getReservedParams(): array
    {
        $result = [];

        foreach (static::DEFAULT as $name => $value) {
            if (empty($this->$name)) {
                continue;
            }

            $result[$name] = $this->$name;
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
        $reservedParams = array_reverse(array_keys(static::DEFAULT)); // Разбор роута начинаем с конца (с действия)

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
        return array_key_exists($name, static::DEFAULT);
    }

    /**
     * Является ли значение пустым.
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        return in_array($value, [null, ''], true);
    }

    /**
     * Является ли название модуля/контроллера/действия валидным.
     * @param string $value
     * @return bool
     */
    protected function validateReserved(string $value): bool
    {
        $pattern = '/^' . self::PATTERN . '$/';
        return preg_match($pattern, $value);
    }

    /**
     * Вернуть дефолтное значение зарезервированного параметра.
     * @param string $name
     * @return string|null
     */
    protected function getDefaultValue(string $name): ?string
    {
        return static::DEFAULT[$name] ?? null;
    }

    /**
     * Указать значение модуля/контроллера/действия.
     * @param string $name
     * @param string|null $value
     * @return void
     */
    protected function setReserved(string $name, ?string $value): void
    {
        if (!$this->isReserved($name)) {
            return;
        }

        if ($this->isEmpty($value)) {
            $this->$name = $this->getDefaultValue($name);
        } elseif ($this->validateReserved($value)) {
            $this->$name = $value;
        }
    }

    /**
     * Указать параметры.
     * @param array $params
     * @return void
     * @todo: валидировать параметры (значениями массива могут быть объекты, массивы и т.д.)
     */
    protected function setParams(array $params): void
    {
        $this->params = $params;
    }
}
