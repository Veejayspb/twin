<?php

namespace twin\route;

/**
 * Class Route
 *
 * @property string|null $module
 * @property string $controller
 * @property string $action
 * @property array $params
 */
class Route
{
    /**
     * Названия и значения зарезервированных параметров.
     */
    const DEFAULT = [
        'module' => null,
        'controller' => 'site',
        'action' => 'index',
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
     * @var string|null
     */
    protected $module = self::DEFAULT['module'];

    /**
     * Контроллер.
     * @var string
     */
    protected $controller = self::DEFAULT['controller'];

    /**
     * Действие.
     * @var string
     */
    protected $action = self::DEFAULT['action'];

    /**
     * Параметры.
     * @var array
     */
    protected $params = [];

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
        $this->setReserved('module', $module);
        $this->setReserved('controller', $controller);
        $this->setReserved('action', $action);
        $this->setParams($params);
    }

    /**
     * @param string $name
     * @return string|array|null
     */
    public function __get(string $name)
    {
        return $this->$name;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        if ($name == 'params') {
            $this->setParams($value);
        } elseif ($this->isReserved($name)) {
            $this->setReserved($name, $value);
        }
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
     * Является ли указанный параметр зарегистрированным.
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
