<?php

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\validator\Email;

final class EmailTest extends BaseTestCase
{
    public function testEmail()
    {
        $items = [
            [
                'value' => 'name01@gmail.com',
                'expected' => true,
            ],
            [
                'value' => 'name@sub.dom-ain.info',
                'expected' => true,
            ],
            [
                'value' => 'ва-ся@домен.рф',
                'expected' => true,
            ],
            [
                'value' => '-name@domain.ru',
                'expected' => false,
            ],
            [
                'value' => '@sub.domain.com',
                'expected' => false,
            ],
            [
                'value' => 'name@',
                'expected' => false,
            ],
            [
                'value' => 'name@domain',
                'expected' => false,
            ],
            [
                'value' => 'name',
                'expected' => false,
            ],
            [
                'value' => '',
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->name = $item['value'];

            $validator = new Email($model, ['name']);

            $this->assertSame(
                $item['expected'],
                $validator->email('name')
            );
        }
    }
}
