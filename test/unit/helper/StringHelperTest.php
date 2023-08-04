<?php

use twin\helper\StringHelper;
use twin\test\helper\BaseTestCase;

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

    public function testGetExtFromName()
    {
        $pairs = [
            'image.jpg' => 'jpg',
            'some.text.txt' => 'txt',
            'noext' => null,
            '' => null,
        ];

        foreach ($pairs as $name => $expected) {
            $ext = StringHelper::getExtFromName($name);
            $this->assertSame($expected, $ext);
        }
    }

    public function testIsServiceAttribute()
    {
        $pairs = [
            'notService' => false,
            '_isService' => true,
            '__alsoService' => true,
        ];

        foreach ($pairs as $name => $expected) {
            $result = StringHelper::isServiceAttribute($name);
            $this->assertSame($expected, $result);
        }
    }
}
