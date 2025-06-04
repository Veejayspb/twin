<?php

use twin\route\Route;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;

final class RouteTest extends BaseTestCase
{
    /**
     * Массив допустимых параметров.
     */
    const PARAMS = [
        'test' => 'param',
        'null' => null,
        'kabob-case' => 'kabob-style',
        'CamelCase' => 'CamelCase',
        0 => 1,
    ];

    /**
     * Названия модуля/контроллера/действия и результат при попытке их присвоить.
     */
    const NAMES = [
        'paramname' => true,
        'param-name' => true,
        'p' => true,
        'param_name' => false,
        'param name' => false,
        '-param-name' => false,
        'param-name-' => false,
        'ParamName' => false,
        '123' => false,
        ' ' => false,
        '-' => false,
    ];

    public function testConstruct()
    {
        $route = new Route;
        $proxy = new ObjectProxy($route); /* @var Route $proxy */
        $this->assertEquals(
            ['', 'main', 'index', []],
            [$proxy->module, $proxy->controller, $proxy->action, $proxy->params]
        );

        $route = new Route(null, null, null, self::PARAMS);
        $proxy = new ObjectProxy($route); /* @var Route $proxy */
        $this->assertSame(
            ['', 'main', 'index', self::PARAMS],
            [$proxy->module, $proxy->controller, $proxy->action, $proxy->params]
        );

        $route = new Route('m', 'c', 'a', self::PARAMS);
        $proxy = new ObjectProxy($route); /* @var Route $proxy */
        $this->assertSame(
            ['m', 'c', 'a', self::PARAMS],
            [$proxy->module, $proxy->controller, $proxy->action, $proxy->params]
        );
    }

    public function testSetProperties()
    {
        ($route = new Route)->setProperties([
            'module' => 'mmm',
        ]);

        $this->assertEquals(
            ['mmm', 'main', 'index', []],
            [$route->module, $route->controller, $route->action, $route->params]
        );

        ($route = new Route)->setProperties([
            'module' => '',
            'controller' => 'test',
            'action' => '',
            'param-one' => 111,
        ]);

        $this->assertEquals(
            ['', 'test', '', ['param-one' => 111]],
            [$route->module, $route->controller, $route->action, $route->params]
        );

        ($route = new Route)->setProperties([
            'controller' => '',
            'action' => 'test',
            'param-two' => null,
        ]);

        $this->assertEquals(
            ['', '', 'test', ['param-two' => null]],
            [$route->module, $route->controller, $route->action, $route->params]
        );
    }

    public function testGetReservedParams()
    {
        $route = new Route('m', 'c', 'a');
        $actual = $route->getReservedParams();

        $this->assertSame([
            'module' => 'm',
            'controller' => 'c',
            'action' => 'a',
        ], $actual);

        $route = new Route(null, 'c', 'a');
        $actual = $route->getReservedParams();

        $this->assertSame([
            'controller' => 'c',
            'action' => 'a',
        ], $actual);
    }

    public function testStringify()
    {
        $route = new Route;
        $proxy = new ObjectProxy($route); /* @var Route $proxy */

        $proxy->module = 'm';
        $proxy->controller = 'c';
        $proxy->action = 'a';

        $this->assertSame('m/c/a', $proxy->stringify());

        $proxy->module = '';

        $this->assertSame('c/a', $proxy->stringify());
    }

    public function testParse()
    {
        $items = [
            'm/c/a' => new Route('m', 'c', 'a'),
            'c/a' => new Route(null, 'c', 'a'),
            'a' => new Route(null, null, 'a'),
            'smth/m/c/a' => new Route('m', 'c', 'a'),
        ];

        foreach ($items as $str => $expected) {
            $route = new Route;
            $route->parse($str);
            $this->assertEquals($expected, $route);
        }
    }
}
