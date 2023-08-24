<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;
use twin\test\helper\TempModel;
use twin\validator\Validator;

final class ValidatorTest extends BaseTestCase
{
    public function testConstructor()
    {
        $model = new TempModel;

        $validator = $this->getMockForAbstractClass(
            Validator::class,
            [$model, ['a', 'b', 'c'], ['message' => 'test message', 'null' => true, 'not_exists' => 'qqq']],
        ); /* @var Validator $validator */

        $proxy = new ObjectProxy($validator);

        $this->assertSame($model, $proxy->model);
        $this->assertSame(['a', 'b', 'c'], $proxy->attributes);
        $this->assertSame('test message', $validator->message);
        $this->assertTrue($validator->null);
        $this->assertFalse(property_exists($validator, 'not_exists'));
    }
}
