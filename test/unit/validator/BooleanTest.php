<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Boolean;

final class BooleanTest extends BaseTestCase
{
    public function testType()
    {
        $items = [
            [
                'value' => true,
                'expected' => true,
            ],
            [
                'value' => false,
                'expected' => true,
            ],
            [
                'value' => 0,
                'expected' => true,
            ],
            [
                'value' => 1,
                'expected' => true,
            ],
            [
                'value' => 2,
                'expected' => false,
            ],
            [
                'value' => 'true',
                'expected' => false,
            ],
            [
                'value' => [false],
                'expected' => false,
            ],
            [
                'value' => null,
                'expected' => false,
            ],
            [
                'value' => '0',
                'expected' => true,
            ],
            [
                'value' => '1',
                'expected' => true,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Boolean($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->type('name')
            );
        }
    }
}
