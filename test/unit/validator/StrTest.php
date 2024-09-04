<?php

namespace test\unit\validator;

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Str;

class StrTest extends BaseTestCase
{
    public function testType()
    {
        $items = [
            [
                'value' => null,
                'expected' => false,
            ],
            [
                'value' => '',
                'expected' => true,
            ],
            [
                'value' => [],
                'expected' => false,
            ],
            [
                'value' => 0,
                'expected' => true,
            ],
            [
                'value' => true,
                'expected' => false,
            ],
            [
                'value' => false,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Str($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->type('name')
            );
        }
    }
}
