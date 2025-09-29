<?php

namespace twin;

use DateTime;
use twin\common\Container;
use twin\common\Exception;
use twin\helper\Alias;
use twin\helper\Request;
use twin\route\Route;

Alias::set('@root', dirname(__DIR__, 3));
Alias::set('@twin', __DIR__);
Alias::set('@self', '@root/app');
Alias::set('@public', '@self/public');
Alias::set('@runtime', '@self/runtime');

class Twin
{
    /**
     * Версия приложения.
     */
    const VERSION = '0.6.0';

    /**
     * Название приложения.
     * @var string
     */
    public string $name = 'Twin Application';

    /**
     * Язык приложения.
     * @var string
     */
    public string $language = 'ru';

    /**
     * Параметры.
     * @var array
     */
    public array $params = [];

    /**
     * DI контейнер.
     * @var Container
     */
    public Container $di;

    /**
     * Индикатор запуска приложения.
     * @var bool
     */
    protected bool $running = false;

    /**
     * Время запуска приложения.
     * @var DateTime
     */
    protected DateTime $date;

    /**
     * Экземпляр приложения.
     * @var static
     */
    protected static self $instance;

    protected function __construct()
    {
        mb_internal_encoding('UTF-8');
        $this->di = new Container;
    }

    protected function __clone() {}

    /**
     * Вернуть экземпляр приложения.
     * @return static
     */
    public static function app(): self
    {
        return static::$instance ??= new static;
    }

    /**
     * Запуск приложения.
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;
        $isConsole = Request::isConsole();

        if ($isConsole) {
            $this->runConsole();
        } else {
            $this->runWeb();
        }
    }

    /**
     * Значение указанного параметра.
     * @param string $name - название параметра в формате: path.to.param
     * @param mixed|null $default - значение, которое вернется, если параметр не найден
     * @return mixed
     */
    public static function param(string $name, mixed $default = null): mixed
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
     * Вернуть дату запуска приложения.
     * @return DateTime
     */
    public static function date(): DateTime
    {
        return static::app()->date ??= new DateTime;
    }

    /**
     * Импорт файла.
     * @param string $alias - алиас пути до файла
     * @param bool $once - использовать require_once
     * @return mixed - FALSE, если файл не существует; TRUE, если $once=true и файл уже был импортирован
     */
    public static function import(string $alias, bool $once = false): mixed
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
     * Запуск веб приложения.
     * @return void
     * @throws Exception
     */
    protected function runWeb(): void
    {
        try {
            $route = $this->di->router->parseUrl(Request::$url);

            if ($route === false) {
                throw new Exception(404);
            }

            $_GET = $route->params;
            $controller = $this->di->router->getController($route->module, $route->controller);

            if (!$controller) {
                throw new Exception(404);
            }

            $controller->runAction($route->action, $route->params);
        } catch (Exception $e) {
            @ob_clean(); // Если исключение выбрасывается во view, то на страницу ошибки выводится часть целевого шаблона
            http_response_code($e->getCode());

            $route = new Route;
            $route->parse(static::app()->di->router->error);
            $route->params = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];

            $controller = $this->di->router->getController($route->module, $route->controller);

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
            $route = $this->di->router->parseUrl((string)$argv[1]);

            if ($route === false) {
                throw new Exception(404);
            }

            unset($argv[0], $argv[1]);
            $route->params = array_values($argv);
            $controller = $this->di->router->getController($route->module, $route->controller);

            if (!$controller) {
                throw new Exception(404, 'Controller not found');
            }

            $controller->runAction($route->action, $route->params);
        } catch (Exception $e) {
            echo "Error {$e->getCode()}: {$e->getMessage()}";
        }
    }
}
