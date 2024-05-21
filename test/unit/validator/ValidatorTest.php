<?php

use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use test\helper\TestModel;
use twin\validator\Validator;

final class ValidatorTest extends BaseTestCase
{
    public function testConstructor()
    {
        $model = new TestModel;

        $validator = $this->getMockForAbstractClass(
            Validator::class,
            [$model, ['id', 'name'], ['message' => 'test message', 'null' => true, 'not_exists' => 'qqq']],
        ); /* @var Validator $validator */

        $proxy = new ObjectProxy($validator);

        $this->assertSame($model, $proxy->model);
        $this->assertSame(['id', 'name'], $proxy->attributes);
        $this->assertSame('test message', $validator->message);
        $this->assertTrue($validator->null);
        $this->assertFalse(property_exists($validator, 'not_exists'));
    }
}
