<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
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

        $model = new TempModel;
        $validator = new Integer($model, ['a']);

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->type($item['value'], 'a')
            );
        }
    }
}
