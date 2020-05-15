<?php

namespace twin;

use twin\cache\CacheInterface;
use twin\common\Component;
use twin\common\Exception;
use twin\controller\ConsoleController;
use twin\controller\WebController;
use twin\helper\Request;
use twin\route\Route;
use twin\route\RouteManager;
use twin\session\Session;
use twin\view\View;

define('LOCALHOST', isset($_SERVER['SERVER_ADDR'], $_SERVER['REMOTE_ADDR']) && $_SERVER['SERVER_ADDR'] == '127.0.0.1' && $_SERVER['REMOTE_ADDR'] == '127.0.0.1');

/**
 * Class Twin
 * @package core
 *
 * @property RouteManager $route
 * @property Session $session
 * @property View $view
 * @property CacheInterface $cache
 */
class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.0.1';

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
    private static $instance;

    private function __construct()
    {
        // Базовые настройки.
        if (LOCALHOST) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        mb_internal_encoding('UTF-8');

        // Установка алиасов.
        static::setAlias('@root', dirname(__DIR__));
        static::setAlias('@app', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app');
        static::setAlias('@core', __DIR__);
        static::setAlias('@web', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'web');
    }

    private function __clone() {}

    private function __wakeup() {}

    /**
     * @param string $name - название компонента
     * @return Component|null
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->components) ? $this->components[$name] : null;
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
            $route = new Route;
            $route->setRoute(Twin::app()->route->error);
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
            if (empty($argv[1])) {
                throw new Exception(500, 'controller/action must be specified');
            }

            $route = new Route();
            $route->setRoute($argv[1]);
            unset($argv[0], $argv[1]);
            $route->params = array_values($argv);

            $namespace = $this->route->getNamespace($route->module);
            ConsoleController::run($namespace, $route);
        } catch (Exception $e) {
            echo "Error: {$e->getMessage()}";
        }
    }

    /**
     * Инстанцировать объект.
     * @param string $class - название класса
     * @param array $properties - свойства класса
     * @return object
     */
    public static function object(string $class, array $properties = [])
    {
        if ($class == self::class) return self::app();
        $object = new $class;
        foreach ($properties as $name => $value) {
            if (!property_exists($object, $name)) continue;
            $object->$name = $value;
        }
        return $object;
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
        $config = array_replace_recursive(
            $this->getDefaultConfig($type),
            $config
        );

        // Присвоение свойств.
        $properties = ['name', 'language', 'params'];
        foreach ($properties as $property) {
            $this->$property = $config[$property];
        }

        // Регистрация компонентов.
        foreach ($config['components'] as $name => $data) {
            if (empty($data)) continue;
            $this->registerComponent($name, $data);
        }
    }

    /**
     * Конфиг по-умолчанию.
     * @param string $type - тип конфига web|console
     * @return array
     */
    private function getDefaultConfig(string $type): array
    {
        $path = static::getAlias("@core/config/$type.php");
        return (file_exists($path)) ? require $path : [];
    }

    /**
     * Регистрация компонента.
     * @param string $name - название компонента
     * @param array $properties - свойства
     * @return bool
     * @throws Exception
     */
    private function registerComponent(string $name, array $properties = []): bool
    {
        if (!array_key_exists('class', $properties)) {
            throw new Exception(500, "Component's class not specified: $name");
        } elseif (!class_exists($properties['class'])) {
            throw new Exception(500, "Component's class not exist: {$properties['class']}");
        }
        $class = $properties['class'];
        $component = new $class($properties); /* @var Component $component */
        if (!is_subclass_of($component, Component::class)) {
            throw new Exception(500, "Component {$properties['class']} must extends " . Component::class);
        }
        $this->components[$name] = $component;
        return true;
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
        self::$aliases[$alias] = $path;
    }

    /**
     * Вернуть путь до алиаса.
     * @param string $alias - "@alias"
     * @return string - path/to/alias
     * @see $aliases
     */
    public static function getAlias(string $alias): string
    {
        preg_match('/^@[a-z]+/', $alias, $matches);
        if (!isset($matches[0])) return $alias;
        $key = $matches[0];
        if (!array_key_exists($key, self::$aliases)) return $alias;
        return str_replace($key, self::$aliases[$key], $alias);
    }
}
