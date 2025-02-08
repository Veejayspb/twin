<?php

namespace twin;

use twin\asset\AssetManager;
use twin\common\Component;
use twin\common\Exception;
use twin\helper\Alias;
use twin\helper\ConfigConstructor;
use twin\helper\Request;
use twin\migration\MigrationManager;
use twin\response\Response;
use twin\route\Route;
use twin\route\RouteManager;
use twin\view\View;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Alias.php';

Alias::set('@root', dirname(__DIR__, 3));
Alias::set('@twin', __DIR__);
Alias::set('@public', $_SERVER['DOCUMENT_ROOT']);
Alias::set('@self', dirname($_SERVER['DOCUMENT_ROOT']));
Alias::set('@runtime', '@self/runtime');
Alias::set('@vendor', '@root/vendor');

/**
 * Class Twin
 *
 * @property-read RouteManager $router
 * @property-read View $view
 * @property-read AssetManager $asset
 * @property-read MigrationManager $migration
 * @property-read Response $response
 */
class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.5.0';

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

    /**
     * @param string $name - название компонента
     * @return Component|null
     */
    public function __get(string $name)
    {
        return $this->getComponent($name);
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
     * @param array $config
     * @return void
     * @throws Exception
     */
    public function run(array $config = []): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;
        $this->registerConfig($config);
        $isConsole = Request::isConsole();

        if ($isConsole) {
            $this->runConsole();
        } else {
            $this->runWeb();
        }
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
     * Вернуть компонент.
     * @param string $name - название компонента
     * @return Component|null
     */
    public function getComponent(string $name): ?Component
    {
        return $this->components[$name] ?? null;
    }

    /**
     * Найти компонент указанного класса.
     * @param string $class - name\space\Component
     * @return Component|null
     */
    public function findComponent(string $class): ?Component
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
     * Регистрация компонента.
     * @param string $name - название компонента
     * @param Component|null $component - объект с компонентом
     * @return void
     */
    public function setComponent(string $name, ?Component $component): void
    {
        if ($component === null) {
            $this->unsetComponent($name);
        } else {
            $this->components[$name] = $component;
        }
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
     * @return mixed|bool - FALSE, если файл не существует; TRUE, если $once=true и файл уже был импортирован
     */
    public static function import(string $alias, bool $once = false)
    {
        $path = Alias::get($alias);

        if (!is_file($path)) {
            return false;
        }

        if ($once) {
            return require_once $path;
        } else {
            return require $path;
        }
    }

    /**
     * Удаление компонента.
     * @param string $name - название компонента
     * @return void
     */
    protected function unsetComponent(string $name): void
    {
        if (array_key_exists($name, $this->components)) {
            unset($this->components[$name]);
        }
    }

    /**
     * Запуск веб приложения.
     * @return void
     * @throws Exception
     */
    protected function runWeb(): void
    {
        try {
            $route = $this->router->parseUrl(Request::$url);

            if ($route === false) {
                throw new Exception(404);
            }

            $_GET = $route->params;
            $controller = $this->router->getController($route->module, $route->controller);

            if (!$controller) {
                throw new Exception(404);
            }

            $controller->runAction($route->action, $route->params);
        } catch (Exception $e) {
            @ob_clean(); // Если исключение выбрасывается во view, то на страницу ошибки выводится часть целевого шаблона
            http_response_code($e->getCode());

            $route = new Route;
            $route->parse(static::app()->router->error);
            $route->params = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            $controller = $this->router->getController($route->module, $route->controller);

            if (!$controller) {
                die('Error action not exists');
            }

            $controller->runAction($route->action, $route->params);
        }
    }

    /**
     * Запуск консольного приложения.
     * @return void
     */
    protected function runConsole(): void
    {
        try {
            global $argv;
            $route = $this->router->parseUrl((string)$argv[1]);

            if ($route === false) {
                throw new Exception(404);
            }

            unset($argv[0], $argv[1]);
            $route->params = array_values($argv);
            $controller = $this->router->getController($route->module, $route->controller);

            if (!$controller) {
                throw new Exception(404, 'Controller not found');
            }

            $controller->runAction($route->action, $route->params);
        } catch (Exception $e) {
            echo "Error {$e->getCode()}: {$e->getMessage()}";
        }
    }

    /**
     * Регистрация конфига.
     * @param array $config - данные пользовательского конфига
     * @return void
     */
    protected function registerConfig(array $config): void
    {
        // Генерация конфига
        $config = new ConfigConstructor($config);
        $data = $config->getData(true);

        // Присвоение свойств
        $this->name = $data['name'] ?? $this->name;
        $this->language = $data['language'] ?? $this->language;
        $this->params = $data['params'] ?? $this->params;

        // Регистрация компонентов
        $components = $data['components'] ?? [];

        foreach ((array)$components as $name => $properties) {
            if (!array_key_exists('class', $properties)) {
                continue;
            }

            $className = $properties['class'];
            $object = new $className($properties);
            $this->setComponent($name, $object);
        }
    }
}
