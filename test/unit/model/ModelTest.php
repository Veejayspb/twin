<?php

use twin\model\Model;
use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;

final class ModelTest extends BaseTestCase
{
    public function testGetLabel()
    {
        $model = $this->getMockBuilder(Model::class)
            ->onlyMethods(['labels'])
            ->getMock();

        $model
            ->expects($this->any())
            ->method('labels')
            ->willReturn([
                'name' => 'value',
            ]);

        $this->assertSame('value', $model->getLabel('name'));
        $this->assertSame('not_exists', $model->getLabel('not_exists'));
    }

    public function testGetHint()
    {
        $model = $this->getMockBuilder(Model::class)
            ->onlyMethods(['hints'])
            ->getMock();

        $model
            ->expects($this->any())
            ->method('hints')
            ->willReturn([
                'name' => 'value',
            ]);

        $this->assertSame('value', $model->getHint('name'));
        $this->assertNull($model->getHint('not_exists'));
    }

    public function testSetError()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);

        $this->assertSame([], $proxy->_errors);

        $model->setError('attribute', 'message');

        $this->assertArrayHasKey('attribute', $proxy->_errors);
        $this->assertSame('message', $proxy->_errors['attribute']);
    }

    public function testSetErrors()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);

        $model->setErrors([
            'attr_one' => 'error 1',
            'attr_two' => 'error 1',
        ]);

        $this->assertSame([
            'attr_one' => 'error 1',
            'attr_two' => 'error 1',
        ], $proxy->_errors);

        $model->setErrors([
            'attr_two' => 'error 2',
            'attr_three' => 'error 2',
        ]);

        $this->assertSame([
            'attr_one' => 'error 1',
            'attr_two' => 'error 2',
            'attr_three' => 'error 2',
        ], $proxy->_errors);

        $model->setErrors([]);
        $this->assertSame([
            'attr_one' => 'error 1',
            'attr_two' => 'error 2',
            'attr_three' => 'error 2',
        ], $proxy->_errors);
    }

    public function testGetError()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);
        $proxy->_errors = [
            'attr' => 'message 1',
        ];

        $this->assertSame('message 1', $model->getError('attr'));
        $this->assertNull($model->getError('not_exists'));
    }

    public function testGetErrors()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);
        $proxy->_errors = [
            'a' => 'error a',
            'b' => 'error b',
        ];

        $this->assertSame([
            'a' => 'error a',
            'b' => 'error b',
        ], $model->getErrors());

        $this->assertSame([
            'a' => 'error a',
        ], $model->getErrors(['a', 'c']));

        $this->assertSame([], $model->getErrors(['d', 1, ['e'], null]));
    }

    public function testHasError()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);
        $proxy->_errors = [
            'a' => 'error a',
        ];

        $this->assertTrue($model->hasError('a'));
        $this->assertFalse($model->hasError('c'));
    }

    public function testHasErrors()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);

        $this->assertFalse($model->hasErrors());

        $proxy->_errors = [
            'a' => 'error a',
        ];

        $this->assertTrue($model->hasErrors());
    }

    public function testClearError()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);
        $proxy->_errors = [
            'a' => 'error a',
        ];

        $model->clearError('b');
        $this->assertSame(['a' => 'error a'], $proxy->_errors);

        $model->clearError('a');
        $this->assertSame([], $proxy->_errors);

    }

    public function testClearErrors()
    {
        $model = $this->getMockForAbstractClass(Model::class);
        $proxy = new ObjectProxy($model);
        $proxy->_errors = [
            'a' => 'error a',
            'b' => 'error b',
        ];

        $model->clearErrors(['c']);
        $this->assertSame([
            'a' => 'error a',
            'b' => 'error b',
        ], $proxy->_errors);

        $model->clearErrors(['a']);
        $this->assertSame([
            'b' => 'error b',
        ], $proxy->_errors);

        $model->clearErrors();
        $this->assertSame([], $proxy->_errors);
    }

    public function testSetAttribute()
    {
        $model = $this->getModel();
        $proxy = new ObjectProxy($model);

        $code = $this->catchExceptionCode(function () use ($model) {
            $model->setAttribute('e', 'new');
        });
        $this->assertSame(0, $code);

        $model->setAttribute('d', 'new');
        $this->assertSame('old', $proxy->d);
        $this->assertSame('old', $model->a);

        $model->setAttribute('_b', 'new');
        $this->assertSame('new', $model->_b);

        $model->setAttribute('a', 'new');
        $this->assertSame('new', $model->a);
    }

    public function testSetAttributes()
    {
        $model = $this->getModel();
        $proxy = new ObjectProxy($model);

        $model->setAttributes([
            'a' => 'new1',
            '_b' => 'new1',
            'c' => 'new1',
            'd' => 'new1',
        ], true);

        $this->assertSame('new1', $model->a);
        $this->assertSame('old', $model->_b);
        $this->assertSame('old', $model::$c);
        $this->assertSame('old', $proxy->d);

        $model->setAttributes([
            'a' => 'new2',
            '_b' => 'new2',
            'c' => 'new2',
            'd' => 'new2',
        ], false);

        $this->assertSame('new2', $model->a);
        $this->assertSame('new2', $model->_b);
        $this->assertSame('old', $model::$c);
        $this->assertSame('old', $proxy->d);
    }

    public function testGetAttributes()
    {
        $model = $this->getModel();

        $this->assertSame([
            'a' => 'old',
        ], $model->getAttributes(['a']));

        $this->assertSame([], $model->getAttributes(['c', 'd']));

        $this->assertSame([
            'a' => 'old',
            '_b' => 'old',
        ], $model->getAttributes());
    }

    public function testHasAttribute()
    {
        $items = [
            'a' => true,
            '_b' => true,
            'c' => false,
            'd' => false,
            'e' => false,
        ];

        $model = $this->getModel();

        foreach ($items as $name => $expected) {
            $this->assertSame($expected, $model->hasAttribute($name));
        }
    }

    public function testIsSafeAttribute()
    {
        $items = [
            'a' => true,
            '_b' => false,
            'c' => false,
            'd' => false,
            'e' => false,
        ];

        $model = $this->getModel();

        foreach ($items as $name => $expected) {
            $this->assertSame($expected, $model->isSafeAttribute($name));
        }
    }

    /**
     * @return Model
     */
    private function getModel(): Model
    {
        return new class extends Model {
            public $a = 'old';
            public $_b = 'old';
            public static $c = 'old';
            protected $d = 'old';

            public function safe(): array
            {
                return ['a'];
            }
        };
    }
}
