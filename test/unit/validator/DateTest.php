<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
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

        $model = new TempModel;
        $validator = new Date($model, ['a']);

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->type($item['value'], 'a')
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

        $model = new TempModel;
        $validator = new Date($model, ['a']);

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->date($item['value'], 'a')
            );
        }
    }
}
