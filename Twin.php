<?php

namespace twin;

use twin\asset\AssetManager;
use twin\common\Component;
use twin\common\Exception;
use twin\controller\ConsoleController;
use twin\controller\WebController;
use twin\helper\ArrayHelper;
use twin\helper\Request;
use twin\route\Route;
use twin\route\RouteManager;
use twin\session\Session;
use twin\view\View;

Twin::setAlias('@root', dirname(__DIR__, 3));
Twin::setAlias('@twin', __DIR__);
Twin::setAlias('@app', '@root/app');
Twin::setAlias('@runtime', '@app/runtime');
Twin::setAlias('@web', '@app/web');
Twin::setAlias('@vendor', '@root/vendor');

spl_autoload_register([Twin::class, 'autoload'], true, true);

/**
 * Class Twin
 * @package core
 *
 * @property RouteManager $route
 * @property Session $session
 * @property View $view
 * @property AssetManager $asset
 */
class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.0.4';

    /**
     * Паттерн алиаса.
     */
    const ALIAS_PATTERN = '@[a-z]+';

    const TYPE_WEB = 'web';
    const TYPE_CONSOLE = 'console';

    /**
     * Название приложения.
     * @var string
     */
    public $name = 'Twin Application';

    /**
     * Язык приложения.
     * @var string
     */
    public $language = 'ru';

    /**
     * Параметры.
     * @var array
     */
    public $params = [];

    /**
     * Индикатор запуска приложения.
     * @var bool
     */
    private $running = false;

    /**
     * Компоненты.
     * @var Component[]
     */
    private $components = [];

    /**
     * Список алиасов путей.
     * @var array
     * @see setAlias()
     * @see getAlias()
     */
    private static $aliases = [];

    /**
     * Экземпляр приложения.
     * @var static
     */
    protected static $instance;

    private function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    private function __clone() {}

    private function __wakeup() {}

    /**
     * @param string $name - название компонента
     * @return Component|null
     */
    public function __get($name)
    {
        return $this->components[$name] ?? null;
    }

    /**
     * Вернуть экземпляр приложения.
     * @return static
     */
    public static function app(): self
    {
        return static::$instance = static::$instance ?: new static;
    }

    /**
     * Запуск приложения.
     * @param array $config - конфигурация
     * @return void
     * @throws Exception
     */
    public function run(array $config = [])
    {
        try {
            if ($this->running) die;
            $this->running = true;

            $this->registerConfig($config, static::TYPE_WEB);

            $route = $this->route->parseUrl(Request::$url);
            if ($route === false) {
                throw new Exception(404);
            }

            $_GET = $route->params;
            $namespace = $this->route->getNamespace($route->module);
            WebController::run($namespace, $route, $this->view);
        } catch (Exception $e) {
            @ob_clean(); // Если исключение выбрасывается во view, то на страницу ошибки выводится часть целевого шаблона
            http_response_code($e->getCode());

            $route = new Route;
            $route->setRoute(Twin::app()->route->error);
            $route->params = ['code' => $e->getCode(), 'message' => $e->getMessage()];

            $namespace = $this->route->getNamespace($route->module);
            WebController::run($namespace, $route, $this->view);
        }
    }

    /**
     * Запуск консольного приложения.
     * @param array $config - конфигурация
     * @return void
     */
    public function runConsole(array $config = [])
    {
        try {
            if ($this->running) die;
            $this->running = true;

            $this->registerConfig($config, static::TYPE_CONSOLE);

            global $argv;

            $route = $this->route->parseUrl((string)$argv[1]);
            if ($route === false) {
                throw new Exception(404);
            }

            unset($argv[0], $argv[1]);
            $route->params = array_values($argv);

            $namespace = $this->route->getNamespace($route->module);
            ConsoleController::run($namespace, $route);
        } catch (Exception $e) {
            echo "Error {$e->getCode()}: {$e->getMessage()}";
        }
    }

    /**
     * Регистрация компонента.
     * @param string $name - название компонента
     * @param string $class - название класса
     * @param array $properties - свойства
     * @return bool
     * @throws Exception
     */
    public function registerComponent(string $name, string $class, array $properties = []): bool
    {
        if (!class_exists($class)) {
            throw new Exception(500, "Component's class not exist: $class");
        }
        if (!is_subclass_of($class, Component::class)) {
            throw new Exception(500, "Component $class must extends " . Component::class);
        }
        if (array_key_exists($name, $this->components)) {
            throw new Exception(500, "Component with name $name already exists");
        }
        if (array_key_exists('class', $properties)) {
            unset($properties['class']);
        }
        $this->components[$name] = new $class($properties);
        return true;
    }

    /**
     * Инстанцировать объект.
     * @param string $class - название класса
     * @param array $properties - свойства класса
     * @return object
     */
    public static function createObject(string $class, array $properties = [])
    {
        if ($class == self::class) {
            return self::app();
        }
        $object = new $class;
        $vars = get_object_vars($object);

        foreach ($properties as $name => $value) {
            if (!array_key_exists($name, $vars)) continue;
            $object->$name = $value;
        }
        return $object;
    }

    /**
     * Значение указанного параметра.
     * @param string $name - название параметра в формате: path.to.param
     * @param null $default - значение, которое вернется, если параметр не найден
     * @return mixed|null
     */
    public static function param(string $name, $default = null)
    {
        $param = static::app()->params;
        $parts = explode('.', $name);

        foreach ($parts as $part) {
            if (array_key_exists($part, $param)) {
                $param = $param[$part];
            } else {
                return $default;
            }
        }
        return $param;
    }

    /**
     * Регистрация конфига.
     * @param array $config - данные пользовательского конфига
     * @param string $type - тип конфига web|console
     * @return void
     */
    private function registerConfig(array $config, string $type)
    {
        // Склейка пользовательского конфига с конфигом по-умолчанию.
        $default = $this->getDefaultConfig($type);
        $config = ArrayHelper::merge(
            $this->prepareConfig($config),
            $this->prepareConfig($default)
        );

        // Присвоение свойств.
        $this->name = $config['name'];
        $this->language = $config['language'];
        $this->params = $config['params'];

        // Регистрация компонентов.
        foreach ($config['components'] as $name => $properties) {
            $class = array_key_exists('class', $properties) ? $properties['class'] : '';
            $this->registerComponent($name, $class, $properties);
        }
    }

    /**
     * Подготовить конфиг, добавив родительские элементы.
     * @param array $config - данные конфига
     * @return array
     * @throws Exception
     */
    private function prepareConfig(array $config): array
    {
        if (!array_key_exists('parent', $config)) {
            return $config;
        }
        $parentConfig = static::import($config['parent']);
        if ($parentConfig === false) {
            throw new Exception(500, "Can't find config file: " . $config['parent']);
        } else {
            unset($config['parent']);
        }
        $parent = $this->prepareConfig($parentConfig);
        return ArrayHelper::merge($parent, $config);
    }

    /**
     * Конфиг по-умолчанию.
     * @param string $type - тип конфига web|console
     * @return array
     */
    private function getDefaultConfig(string $type): array
    {
        $alias = "@twin/config/$type.php";
        $config = static::import($alias);
        return $config ?: [];
    }

    /**
     * Установить алиас пути.
     * @param string $alias - "@alias"
     * @param string $path - path/to/alias
     * @return void
     * @see $aliases
     */
    public static function setAlias(string $alias, string $path)
    {
        $pattern = '/^' . static::ALIAS_PATTERN . '$/';
        if (preg_match($pattern, $alias)) {
            self::$aliases[$alias] = $path;
        }
    }

    /**
     * Вернуть путь до алиаса.
     * @param string $alias - "@alias"
     * @return string - path/to/alias
     * @see $aliases
     */
    public static function getAlias(string $alias): string
    {
        $pattern = '/^' . static::ALIAS_PATTERN . '/';
        preg_match($pattern, $alias, $matches);

        if (!isset($matches[0])) {
            return $alias;
        }

        $key = $matches[0];

        if (!array_key_exists($key, self::$aliases)) {
            return $alias;
        }
        $result = str_replace($key, self::$aliases[$key], $alias);

        // Если в пути остался алиас, то выполнить повторное преобразование
        if (preg_match($pattern, $result)) {
            return static::getAlias($result);
        }
        return $result;
    }

    /**
     * Импорт файла.
     * @param string $alias - алиас пути до файла
     * @param bool $once - использовать require_once
     * @return mixed|bool - FALSE, если файл не существует, TRUE, если $once=true и файл уже был импортирован
     */
    public static function import(string $alias, bool $once = false)
    {
        $path = static::getAlias($alias);
        if (!is_file($path)) return false;

        if ($once) {
            return require_once $path;
        } else {
            return require $path;
        }
    }

    /**
     * Автозагрузка классов.
     * @param string $className - название класса
     * @return void
     */
    public static function autoload(string $className)
    {
        $className = str_replace('\\', '/', $className);

        if (substr($className, 0, 4) == 'twin') {
            $alias = "@$className.php";
        } else {
            $alias = "@root/$className.php";
        }
        static::import($alias, true);
    }
}
