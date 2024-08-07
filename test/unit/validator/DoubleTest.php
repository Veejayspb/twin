<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Double;

final class DoubleTest extends BaseTestCase
{
    public function testType()
    {
        $items = [
            [
                'value' => 1,
                'expected' => true,
            ],
            [
                'value' => 2.2,
                'expected' => true,
            ],
            [
                'value' => '33',
                'expected' => true,
            ],
            [
                'value' => '4.4',
                'expected' => true,
            ],
            [
                'value' => [5],
                'expected' => false,
            ],
            [
                'value' => null,
                'expected' => false,
            ],
            [
                'value' => true,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Double($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->type('name')
            );
        }
    }
}
