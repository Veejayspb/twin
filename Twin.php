<?php

namespace twin;

use twin\asset\AssetManager;
use twin\common\Component;
use twin\common\Exception;
use twin\controller\ConsoleController;
use twin\controller\WebController;
use twin\helper\Alias;
use twin\helper\ConfigConstructor;
use twin\helper\Request;
use twin\migration\MigrationManager;
use twin\response\Response;
use twin\route\Route;
use twin\route\RouteManager;
use twin\view\View;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Alias.php';
spl_autoload_register([Twin::class, 'autoload'], true, true);

Alias::set('@root', dirname(__DIR__, 3));
Alias::set('@twin', __DIR__);
Alias::set('@web', $_SERVER['DOCUMENT_ROOT']);
Alias::set('@self', dirname($_SERVER['DOCUMENT_ROOT']));
Alias::set('@runtime', '@self/runtime');
Alias::set('@vendor', '@root/vendor');

/**
 * Class Twin
 *
 * @property RouteManager $route
 * @property View $view
 * @property AssetManager $asset
 * @property MigrationManager $migration
 * @property Response $response
 */
class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.4.0';

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
    protected $running = false;

    /**
     * Компоненты.
     * @var Component[]
     */
    protected $components = [];

    /**
     * Экземпляр приложения.
     * @var static
     */
    protected static $instance;
    
    protected function __construct()
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

            $this->registerConfig($config, ConfigConstructor::WEB);

            $route = $this->route->parseUrl(Request::$url);
            if ($route === false) {
                throw new Exception(404);
            }

            $_GET = $route->params;
            $namespace = $this->route->getNamespace($route->module);
            WebController::run($namespace, $route);
        } catch (Exception $e) {
            @ob_clean(); // Если исключение выбрасывается во view, то на страницу ошибки выводится часть целевого шаблона
            http_response_code($e->getCode());

            $route = new Route;
            $route->setRoute(Twin::app()->route->error);
            $route->params = ['code' => $e->getCode(), 'message' => $e->getMessage()];

            $namespace = $this->route->getNamespace($route->module);
            WebController::run($namespace, $route);
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

            $this->registerConfig($config, ConfigConstructor::CONSOLE);

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
     * @param Component $component - объект с компонентом
     * @return void
     */
    public function setComponent(string $name, Component $component)
    {
        $this->components[$name] = $component;
    }

    /**
     * Список компонентов.
     * @return Component[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Вернуть компонент указанного класса.
     * @param string $class - name\space\Component
     * @return Component|null
     */
    public function getComponent(string $class)
    {
        $components = $this->getComponents();

        foreach ($components as $component) {
            if (is_a($component, $class)) {
                return $component;
            }
        }

        return null;
    }

    /**
     * Значение указанного параметра.
     * @param string $name - название параметра в формате: path.to.param
     * @param mixed|null $default - значение, которое вернется, если параметр не найден
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
     * Импорт файла.
     * @param string $alias - алиас пути до файла
     * @param bool $once - использовать require_once
     * @return mixed|bool - FALSE, если файл не существует, TRUE, если $once=true и файл уже был импортирован
     */
    public static function import(string $alias, bool $once = false)
    {
        $path = Alias::get($alias);
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

    /**
     * Регистрация конфига.
     * @param array $config - данные пользовательского конфига
     * @param string $type - тип конфига web|console
     * @return void
     */
    private function registerConfig(array $config, string $type)
    {
        // Генерация конфига
        $config = new ConfigConstructor($config);
        $data = $config->registerDefault($type)->data();

        // Присвоение свойств
        $this->name = $data['name'];
        $this->language = $data['language'];
        $this->params = $data['params'];

        // Регистрация компонентов
        foreach ($config->getComponents() as $name => $component) {
            $this->setComponent($name, $component);
        }
    }
}
