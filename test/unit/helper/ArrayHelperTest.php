<?php

use twin\helper\ArrayHelper;
use test\helper\BaseTestCase;

final class ArrayHelperTest extends BaseTestCase
{
    const ARRAY = [
        1 => 'one',
        null => 'two', // Ключ NULL превратится в пустую строку: ""
        '3' => 'three',
        'four' => 4,
    ];

    public function testColumn()
    {
        $actual = ArrayHelper::column(
            self::ARRAY,
            function ($k, $v) {
                return $k;
            },
            function ($k, $v) {
                return "$k - $v";
            }
        );

        $expected = [
            1 => '1 - one',
            '' => ' - two',
            '3' => '3 - three',
            'four' => 'four - 4',
        ];

        $this->assertSame($expected, $actual);

        $actual = ArrayHelper::column(
            self::ARRAY,
            function ($k, $v) {
                return 5;
            },
            function ($k, $v) {
                return $v;
            }
        );

        $expected = [
            5 => 4,
        ];

        $this->assertSame($expected, $actual);
    }

    public function testHasElements()
    {
        $items = [
            [
                'elements' => [1 => 'one'],
                'expected' => true,
            ],
            [
                'elements' => ['3' => 'three'],
                'expected' => true,
            ],
            [
                'elements' => [2 => 'two'],
                'expected' => false,
            ],
            [
                'elements' => [1 => 'one', 2 => 'two'],
                'expected' => false,
            ],
            [
                'elements' => ['four' => '4'],
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $actual = ArrayHelper::hasElements(self::ARRAY, $item['elements']);
            $this->assertSame($item['expected'], $actual);
        }
    }

    public function testFindByParams()
    {
        $collection = [
            1 => [
                'one' => 1,
                2 => 'two',
            ],
            2 => [
                'two' => 22,
                4 => ['four'],
            ],
            3 => new class {
                public $one = 1;
            },
            4 => new class {
                public $two = 22;
                public $three = 3;
            },
        ];

        $items = [
            [
                'params' => ['one' => 1],
                'expected' => 1,
            ],
            [
                'params' => [4 => ['four']],
                'expected' => 2,
            ],
            [
                'params' => ['two' => 22, 'three' => 3],
                'expected' => 4,
            ],
            [
                'params' => ['five' => 5],
                'expected' => false,
            ],
            [
                'params' => [],
                'expected' => 1,
            ],
        ];

        foreach ($items as $item) {
            $actual = ArrayHelper::findByParams($collection, $item['params']);
            $this->assertSame($item['expected'], $actual);
        }

        $actual = ArrayHelper::findByParams([], []);
        $this->assertSame(false, $actual);
    }

    public function testStringExpression()
    {
        $array = [
            'q' => 'w',
            2 => 'e',
        ];

        $actual = ArrayHelper::stringExpression($array, function ($k, $v) {
            return "$k + $v";
        }, ', ');

        $this->assertSame('q + w, 2 + e', $actual);
    }

    public function testMerge()
    {
        $first = [
            0 => 'zero',
            1 => 'one',
            2 => [
                3 => 'three',
            ],
        ];

        $second = [
            1 => 'test',
            2 => [
                3 => ['test'],
                4 => 'test',
            ],
        ];

        $expected = [
            0 => 'zero',
            1 => 'one',
            2 => [
                3 => 'three',
                4 => 'test',
            ],
        ];

        $actual = ArrayHelper::merge($first, $second);
        $this->assertSame($expected, $actual);
    }

    public function testKeysExist()
    {
        $items = [
            [
                'keys' => [1, 'four', ''],
                'only' => false,
                'result' => true,
            ],
            [
                'keys' => [1, '', '3', 'four'],
                'only' => true,
                'result' => true,
            ],
            [
                'keys' => [1, '', '3', 'four', 'five'], // Ключ five отсутствует
                'only' => false,
                'result' => false,
            ],
            [
                'keys' => [1, '', '3', 'four', 'five'], // Ключ five отсутствует
                'only' => true,
                'result' => false,
            ],
            [
                'keys' => [false], // Ключ может быть только числом или строкой
                'only' => false,
                'result' => false,
            ],
            [
                'keys' => [2, 'four'], // Ключ 2 отсутствует
                'only' => false,
                'result' => false,
            ],
            [
                'keys' => [2, 'four'],
                'only' => true, // Присутствуют также другие ключи
                'result' => false,
            ],
        ];

        foreach ($items as $item) {
            $actual = ArrayHelper::keysExist($item['keys'], self::ARRAY, $item['only']);
            $this->assertSame($item['result'], $actual);
        }
    }
}
