<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Required;

final class RequiredTest extends BaseTestCase
{
    public function testNotEmpty()
    {
        $items = [
            [
                'value' => null,
                'expected' => false,
            ],
            [
                'value' => '',
                'expected' => false,
            ],
            [
                'value' => ' ',
                'expected' => true,
            ],
            [
                'value' => [],
                'expected' => true,
            ],
            [
                'value' => 0,
                'expected' => true,
            ],
            [
                'value' => true,
                'expected' => true,
            ],
            [
                'value' => false,
                'expected' => true,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Required($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->notEmpty('name')
            );
        }
    }
}
