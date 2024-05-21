<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
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
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = $this->getMockForAbstractClass(
                Range::class,
                [$model, ['name'], ['range' => $item['range']]]
            );

            $this->assertSame(
                $item['expected'],
                $validator->range('name')
            );
        }
    }
}
