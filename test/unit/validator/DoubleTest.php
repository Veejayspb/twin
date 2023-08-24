<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
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

        $model = new TempModel;
        $validator = new Double($model, ['a']);

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->type($item['value'], 'a')
            );
        }
    }
}
