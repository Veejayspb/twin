<?php

use twin\test\helper\BaseTestCase;
use twin\test\helper\TempModel;
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
        ];

        $model = new TempModel;
        $validator = new Boolean($model, ['a']);

        foreach ($items as $item) {
            $this->assertSame(
                $item['expected'],
                $validator->type($item['value'], 'a')
            );
        }
    }
}
