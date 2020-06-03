<?php

namespace twin;

use twin\asset\AssetManager;
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
 * @property AssetManager $asset
 */
class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.0.3';

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
        // Базовые настройки.
        if (LOCALHOST) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
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
            if (empty($argv[1])) {
                throw new Exception(500, 'controller/action must be specified');
            }

            $route = $this->route->parseUrl($argv[1]);
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
     * Инстанцировать объект.
     * @param string $class - название класса
     * @param array $properties - свойства класса
     * @return object
     */
    public static function createObject(string $class, array $properties = [])
    {
        if ($class == self::class) return self::app();
        $object = new $class;
        $vars = get_object_vars($object);
        foreach ($properties as $name => $value) {
            if (!array_key_exists($name, $vars)) continue;
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
        $default = $this->getDefaultConfig($type);
        $config = array_replace_recursive(
            $this->prepareConfig($default),
            $this->prepareConfig($config)
        );

        // Присвоение свойств.
        $properties = ['name', 'language', 'params'];
        foreach ($properties as $property) {
            $this->$property = $config[$property];
        }

        // Регистрация алиасов.
        $aliases = array_key_exists('aliases', $config) ? $config['aliases'] : [];
        foreach ($aliases as $alias => $path) {
            static::setAlias($alias, $path);
        }

        // Регистрация компонентов.
        foreach ($config['components'] as $name => $data) {
            if (empty($data)) continue;
            $this->registerComponent($name, $data);
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
        if (!array_key_exists('parent', $config)) return $config;
        $path = $config['parent'];
        unset($config['parent']);
        if (!is_file($path)) {
            throw new Exception(500, "Can't find config file: $path");
        }
        $parent = $this->prepareConfig(include $path);
        return array_replace_recursive($parent, $config);
    }

    /**
     * Конфиг по-умолчанию.
     * @param string $type - тип конфига web|console
     * @return array
     */
    private function getDefaultConfig(string $type): array
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $type . '.php';
        return require $path;
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
        if (!isset($matches[0])) return $alias;
        $key = $matches[0];
        if (!array_key_exists($key, self::$aliases)) return $alias;
        $result = str_replace($key, self::$aliases[$key], $alias);
        // Если в пути остался алиас, то выполнить повторное преобразование
        if (preg_match($pattern, $result)) {
            return static::getAlias($result);
        }
        return $result;
    }
}
