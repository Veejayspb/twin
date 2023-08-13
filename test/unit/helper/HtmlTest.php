<?php

use twin\helper\Html;
use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;

final class HtmlTest extends BaseTestCase
{
    const OPTIONS = [
        0 => 'zero',
        1 => 'one',
        2 => 'two',
    ];

    public function testEncode()
    {
        $result = Html::encode('<a href="#">Ленин в ссылке</a>');
        $this->assertSame('&lt;a href=&quot;#&quot;&gt;Ленин в ссылке&lt;/a&gt;', $result);
    }

    public function testTagOpen()
    {
        $result = Html::tagOpen('div', ['class' => 'btn btn-default']);
        $this->assertSame('<div class="btn btn-default">', $result);
    }

    public function testTagClose()
    {
        $result = Html::tagClose('div');
        $this->assertSame('</div>', $result);

        $result = Html::tagClose('br');
        $this->assertSame('</br>', $result);
    }

    public function testTag()
    {
        $result = Html::tag('a', ['href' => '/', 'class' => 'test'], 'text');
        $this->assertSame('<a href="/" class="test">text</a>', $result);

        $result = Html::tag('hr', ['class' => 'qqq'], 'inactive-text');
        $this->assertSame('<hr class="qqq">', $result);

        $result = Html::tag('unknown', ['class' => 'test'], 'text');
        $this->assertSame('<unknown class="test">text</unknown>', $result);
    }

    public function testA()
    {
        $result = Html::a('/link/to/page', 'text', ['class' => 'primary']);
        $this->assertSame('<a class="primary" href="/link/to/page">text</a>', $result);
    }

    public function testImg()
    {
        $result = Html::img('/image.jpg', ['alt' => 'img', 'hide' => true]);
        $this->assertSame('<img alt="img" hide src="/image.jpg">', $result);
    }

    public function testLabel()
    {
        $result = Html::label('name', ['id' => 'some-id']);
        $this->assertSame('<label id="some-id">name</label>', $result);
    }

    public function testSubmit()
    {
        $result = Html::submit('ok');
        $this->assertSame('<input type="submit" value="ok">', $result);

        $result = Html::submit('ok', ['type' => 'number']);
        $this->assertSame('<input type="number" value="ok">', $result);
    }

    public function testInputText()
    {
        $result = Html::inputText('some-text', ['class' => 'eee']);
        $this->assertSame('<input class="eee" type="text" value="some-text">', $result);
    }

    public function testInputEmail()
    {
        $result = Html::inputEmail('babushke@na.derevnu', ['class' => 'ttt']);
        $this->assertSame('<input class="ttt" type="email" value="babushke@na.derevnu">', $result);
    }

    public function testInputPassword()
    {
        $result = Html::inputPassword('qwerty123', ['class' => 'pass', 'value' => 'test']);
        $this->assertSame('<input class="pass" value="qwerty123" type="password">', $result);
    }

    public function testInputHidden()
    {
        $result = Html::inputHidden('val', ['class' => 'invisible']);
        $this->assertSame('<input class="invisible" type="hidden" value="val">', $result);
    }

    public function testInputFile()
    {
        $result = Html::inputFile(['multiple' => true]);
        $this->assertSame('<input multiple type="file">', $result);
    }

    public function testTextArea()
    {
        $result = Html::textArea('some text', ['class' => 'yyy']);
        $this->assertSame('<textarea class="yyy">some text</textarea>', $result);
    }

    public function testSelect()
    {
        $result = Html::select(1, self::OPTIONS, ['multiple' => false]);

        $expected = '<select>';
        $expected.= '<option value="0">zero</option>';
        $expected.= '<option value="1" selected>one</option>';
        $expected.= '<option value="2">two</option>';
        $expected.= '</select>';

        $this->assertSame($expected, $result);

        $result = Html::select([0, 2], self::OPTIONS, ['multiple' => true]);

        $expected = '<select multiple>';
        $expected.= '<option value="0" selected>zero</option>';
        $expected.= '<option value="1">one</option>';
        $expected.= '<option value="2" selected>two</option>';
        $expected.= '</select>';

        $this->assertSame($expected, $result);
    }

    public function testRadio()
    {
        $result = Html::radio(1, self::OPTIONS, ['class' => 'jjj'], '-');

        $expected[] = '<label><input class="jjj" type="radio" name="name-1" value="0"> zero</label>';
        $expected[] = '<label><input class="jjj" type="radio" name="name-1" value="1" checked> one</label>';
        $expected[] = '<label><input class="jjj" type="radio" name="name-1" value="2"> two</label>';

        $this->assertSame(implode('-', $expected), $result);
    }

    public function testCheckbox()
    {
        $result = Html::checkbox(1, ['checked' => false]);
        $this->assertSame('<input type="checkbox" value="1">', $result);

        $result = Html::checkbox('test', ['checked' => true]);
        $this->assertSame('<input checked type="checkbox" value="test">', $result);
    }

    public function testUniqueStr()
    {
        $proxy = new ObjectProxy(new Html);
        $proxy->uniqueNumber = 0;

        $result = Html::uniqueStr('test-{num}');
        $this->assertSame('test-1', $result);

        $result = Html::uniqueStr('test-{num}');
        $this->assertSame('test-2', $result);
    }

    public function testAddCssClass()
    {
        $attributes = [
            'class' => 'one two',
            'id' => 'test-id',
        ];

        $expected = $attributes;
        $expected['class'] = 'one two three';

        Html::addCssClass($attributes, 'three');
        $this->assertSame($expected, $attributes);

        Html::addCssClass($attributes, 'two');
        $this->assertSame($expected, $attributes);
    }
}
