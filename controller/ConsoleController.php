<?php

namespace twin\controller;

use twin\common\Exception;
use twin\response\ResponseConsole;
use twin\route\Route;
use ReflectionMethod;
use twin\Twin;

abstract class ConsoleController extends Controller
{
    /**
     * Help list.
     * @var array
     */
    protected $help = [
        'help - reference',
    ];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        Twin::app()->registerComponent('response', new ResponseConsole);
    }

    /**
     * Ссылка на список команд.
     * @return array
     */
    public function index()
    {
        return $this->help();
    }

    /**
     * Список команд.
     * @return array
     */
    public function help()
    {
        return $this->help;
    }

    /**
     * Вызвать указанные контроллер/действие.
     * @param string $namespace - неймспейс контроллера
     * @param Route $route - роут
     * @return void
     * @throws Exception
     */
    public static function run(string $namespace, Route $route)
    {
        if (self::class != get_called_class()) {
            throw new Exception(500, 'Denied to run controller not from class: ' . self::class);
        }

        $controller = static::$instance = static::getController($namespace, $route->controller);
        $controller->route = $route;
        $controller->init();

        $action = static::getActionName($route->action);

        if (!$controller->actionExists($action)) {
            throw new Exception(404);
        }

        $controller->beforeAction($action);
        $data = $controller->callAction($action, $route->params);

        echo Twin::app()->response->run($data);
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function callAction(string $action, array $params)
    {
        $reflection = new ReflectionMethod($this, $action);
        $parameters = $reflection->getParameters();
        $result = [];

        foreach ($parameters as $i => $parameter) {
            if (array_key_exists($i, $params)) {
                $result[] = $params[$i];
            } elseif (!$parameter->isOptional()) {
                throw new Exception(400, 'Required property is not specified: ' . $parameter->name);
            }
        }

        return call_user_func_array([$this, $action], $result);
    }
}
