<?php

namespace twin\controller;

use ReflectionMethod;
use twin\common\Exception;

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
     * Ссылка на список команд.
     * @return array
     */
    public function actionIndex()
    {
        return $this->actionHelp();
    }

    /**
     * Список команд.
     * @return array
     */
    public function actionHelp()
    {
        return $this->help;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function action(string $action, array $params)
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
