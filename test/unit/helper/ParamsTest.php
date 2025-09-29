<?php

use test\helper\BaseTestCase;
use twin\helper\Params;

final class ParamsTest extends BaseTestCase
{
    const DATA = [
        'int' => 1,
        'string' => 'test',
        'array' => [],
        'null' => null,
        'bool' => false,
    ];

    public function testConstruct()
    {
        $params = new Params(self::DATA);
        $reflectionProperty = new ReflectionProperty($params, 'data');
        $data = $reflectionProperty->getValue($params);

        $this->assertSame(self::DATA, $data);
    }

    public function testGet()
    {
        $params = new Params(self::DATA);
        $reflectionProperty = new ReflectionProperty($params, 'data');
        $data = $reflectionProperty->getValue($params);

        foreach (self::DATA as $key => $value) {
            $this->assertSame($data[$key], $params->$key);
        }

        $this->assertNull($params->undefined);
    }

    public function testSet()
    {
        $expected = self::DATA;

        $params = new Params(self::DATA);
        $params->test = $expected['test'] = 'test';
        $params->int  = $expected['int']  = 2;

        $reflectionProperty = new ReflectionProperty($params, 'data');
        $data = $reflectionProperty->getValue($params);

        $this->assertSame($expected, $data);
    }
}
