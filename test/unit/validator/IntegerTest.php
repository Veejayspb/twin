<?php

use test\helper\BaseTestCase;
use test\helper\TempModel;
use twin\validator\Integer;

final class IntegerTest extends BaseTestCase
{
    public function testType()
    {
        $items = [
            [
                'value' => 7,
                'expected' => true,
            ],
            [
                'value' => -7,
                'expected' => true,
            ],
            [
                'value' => '88',
                'expected' => true,
            ],
            [
                'value' => '-999',
                'expected' => true,
            ],
            [
                'value' => 3.3,
                'expected' => false,
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
            $model = new TempModel;
            $model->a = $item['value'];

            $validator = new Integer($model, ['a']);

            $this->assertSame(
                $item['expected'],
                $validator->type('a')
            );
        }
    }
}
