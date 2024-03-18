<?php

use test\helper\BaseTestCase;
use test\helper\TempModel;
use twin\validator\Numeric;

final class NumericTest extends BaseTestCase
{
    public function testMin()
    {
        $items = [
            [
                'value' => 4,
                'expected' => true,
            ],
            [
                'value' => '6',
                'expected' => true,
            ],
            [
                'value' => 7.7,
                'expected' => true,
            ],
            [
                'value' => 3,
                'expected' => false,
            ],
            [
                'value' => -4,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TempModel;
            $model->a = $item['value'];

            $validator = $this->getMockForAbstractClass(
                Numeric::class,
                [$model, ['a'], ['min' => 4]]
            );

            $this->assertSame(
                $item['expected'],
                $validator->min('a')
            );
        }
    }

    public function testMax()
    {
        $items = [
            [
                'value' => 4,
                'expected' => true,
            ],
            [
                'value' => '3',
                'expected' => true,
            ],
            [
                'value' => -6.8,
                'expected' => true,
            ],
            [
                'value' => 5,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TempModel;
            $model->a = $item['value'];

            $validator = $this->getMockForAbstractClass(
                Numeric::class,
                [$model, ['a'], ['max' => 4]]
            );

            $this->assertSame(
                $item['expected'],
                $validator->max('a')
            );
        }
    }
}
