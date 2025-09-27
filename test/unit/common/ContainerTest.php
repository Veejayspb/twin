<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\common\Container;

final class ContainerTest extends BaseTestCase
{
    const ID = 'testId';

    public function testSet()
    {
        $c = $this->getContainer();
        $class = TestModel::class;
        $model = new $class;

        // При создании контейнера все зависимости пусты
        $this->assertSame([], $c->definitions);
        $this->assertSame([], $c->instances);

        // Добавление зависимости стрелочной функцией (название класса в кач-ве ID)
        $c->set($class, fn() => $model);
        $this->assertArrayHasKey($class, $c->definitions);
        $this->assertEquals($model, $c->definitions[$class]());

        // Добавление зависимости анонимной функцией (строка в кач-ве ID)
        $c->set(self::ID, function () use ($model) {
            return $model;
        });
        $this->assertArrayHasKey(self::ID, $c->definitions);
        $this->assertEquals($model, $c->definitions[self::ID]());
    }

    public function testGet()
    {
        $c = $this->getContainer();
        $model = new TestModel;

        $c->definitions[self::ID] = fn() => $model;
        $actual = $c->get(self::ID);
        $this->assertSame($model, $actual);
        $this->assertArrayHasKey(self::ID, $c->instances);
        $this->assertSame($model, $c->instances[self::ID]);

        $this->expectExceptionCode(500);
        $c->get('undefined');
    }

    public function testHas()
    {
        $c = $this->getContainer();

        $actual = $c->has(self::ID);
        $this->assertFalse($actual);

        $c->definitions[self::ID] = fn() => new TestModel;
        $actual = $c->has(self::ID);
        $this->assertTrue($actual);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return new class extends Container
        {
            public array $definitions = [];
            public array $instances = [];
        };
    }
}
