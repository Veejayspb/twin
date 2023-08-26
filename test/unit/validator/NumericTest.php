<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
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

        $model = new TempModel;
        $validator = $this->getMockForAbstractClass(
            Numeric::class,
            [$model, ['a'], ['min' => 4]]
        );

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->min($item['value'], 'a')
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

        $model = new TempModel;
        $validator = $this->getMockForAbstractClass(
            Numeric::class,
            [$model, ['a'], ['max' => 4]]
        );

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->max($item['value'], 'a')
            );
        }
    }
}
