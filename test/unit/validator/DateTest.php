<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Date;

final class DateTest extends BaseTestCase
{
    public function testType()
    {
        $items = [
            [
                'value' => '24-08-2023',
                'expected' => true,
            ],
            [
                'value' => 'qqq',
                'expected' => true,
            ],
            [
                'value' => 24,
                'expected' => false,
            ],
            [
                'value' => true,
                'expected' => false,
            ],
            [
                'value' => null,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Date($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->type('name')
            );
        }
    }

    public function testDate()
    {
        $items = [
            [
                'value' => '2023-08-24',
                'expected' => true,
            ],
            [
                'value' => '9999-99-99',
                'expected' => false,
            ],
            [
                'value' => '23-08-24',
                'expected' => false,
            ],
            [
                'value' => '24-08-2023',
                'expected' => false,
            ],
            [
                'value' => '2023/08/24',
                'expected' => false,
            ],
            [
                'value' => '2023.08.24',
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Date($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->date('name')
            );
        }
    }
}
