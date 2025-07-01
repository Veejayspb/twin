<?php

use twin\helper\StringHelper;
use test\helper\BaseTestCase;

final class StringHelperTest extends BaseTestCase
{
    public function testUcfirst()
    {
        $pairs = [
            'qqq' => 'Qqq',
            'WWW' => 'WWW',
            '1234' => '1234',
            '-jjj' => '-jjj',
            '  ' => '  ',
            'ыыы' => 'Ыыы',
            'ЯЯЯ' => 'ЯЯЯ',
        ];

        foreach ($pairs as $original => $expected) {
            $result = StringHelper::ucfirst($original);
            $this->assertSame($expected, $result);
        }
    }

    public function testWordEnding()
    {
        foreach ([0, 1, 2, 4, 5] as $i) {
            $variants = array_fill(0, $i, '# ед');
            $result = StringHelper::wordEnding(1, $variants);
            $this->assertSame('', $result);
        }

        $pairs = [
            0 => 'единиц',
            1 => 'единица',
            2 => 'единицы',
            5 => 'единиц',
            11 => 'единиц',
            12 => 'единиц',
            15 => 'единиц',
            121 => 'единица',
            122 => 'единицы',
            125 => 'единиц',
        ];

        foreach ($pairs as $i => $word) {
            $result = StringHelper::wordEnding($i, ['единица', 'единицы', 'единиц']);
            $this->assertSame($word, $result);
        }

        $result = StringHelper::wordEnding(1, ['# ед', '# ед', '# ед']);
        $this->assertSame('1 ед', $result);

        $result = StringHelper::wordEnding(2, ['# ед #', '# ед #', '# ед #']);
        $this->assertSame('2 ед 2', $result);
    }

    public function testCamelToKabob()
    {
        $pairs = [
            'AnyName' => 'any-name',
            'anyName' => 'any-name',
            '' => '',
            'any-name' => 'anyname',
            'Any-Name' => 'any-name',
            'Any--Name' => 'any-name',
            'Name' => 'name',
            ' Any Name ' => 'any-name',
            ' Any name ' => 'anyname',
            '!Any_Name+' => 'any-name',
            '0anyName' => '0any-name',
            'ANYNAME' => 'a-n-y-n-a-m-e',
        ];

        foreach ($pairs as $camel => $kabob) {
            $actual = StringHelper::camelToKabob($camel);
            $this->assertSame($kabob, $actual);
        }
    }

    public function testKabobToCamel()
    {
        $pairs = [
            'any-name' => 'AnyName',
            'AnyName' => 'Anyname',
            '-any-name-' => 'AnyName',
            '!any+name_' => 'Anyname',
            '0any-name-1' => '0anyName1',
            '' => '',
            ' anyname ' => 'Anyname',
            'a-n-y-n-a-m-e' => 'ANYNAME',
            '--any--name--' => 'AnyName',
            '!@#$%^&*()' => '',
        ];

        foreach ($pairs as $kabob => $camel) {
            $actual = StringHelper::kabobToCamel($kabob);
            $this->assertSame($camel, $actual);
        }
    }

    public function testSlug()
    {
        $pairs = [
            'абвгдеёжзийклмнопрстуфхцчшщъыьэюя' => 'abvgdeyozhzijklmnoprstufhtschshschyeyuya',
            ' -  - ' => '_-__-_',
            '0123456789' => '0123456789',
            'abc-абв' => 'abc-abv',
            '!@#$%^&*()+' => '',
        ];

        foreach ($pairs as $original => $translit) {
            $actual = StringHelper::slug($original);
            $this->assertSame($translit, $actual);
        }
    }
}
