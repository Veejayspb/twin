<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
use twin\validator\Range;

final class RangeTest extends BaseTestCase
{
    public function testRange()
    {
        $items = [
            [
                'value' => 1,
                'range' => [],
                'expected' => true,
            ],
            [
                'value' => 2,
                'range' => [2],
                'expected' => true,
            ],
            [
                'value' => 3,
                'range' => ['3'],
                'expected' => true,
            ],
            [
                'value' => null,
                'range' => [0],
                'expected' => true,
            ],
            [
                'value' => '',
                'range' => [0],
                'expected' => true,
            ],
            [
                'value' => 1,
                'range' => ['1', '2'],
                'expected' => true,
            ],
            [
                'value' => 10,
                'range' => [20],
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TempModel;
            $model->a = $item['value'];

            $validator = $this->getMockForAbstractClass(
                Range::class,
                [$model, ['a'], ['range' => $item['range']]]
            );

            $this->assertSame(
                $item['expected'],
                $validator->range('a')
            );
        }
    }
}
