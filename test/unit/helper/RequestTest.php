<?php

use twin\helper\Request;
use test\helper\BaseTestCase;

final class RequestTest extends BaseTestCase
{
    public function testIsAjax()
    {
        $this->assertFalse(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'AnotherHttpRequest';
        $this->assertFalse(Request::isAjax());
    }

    public function testIsConsole()
    {
        $actual = Request::isConsole();
        $this->assertTrue($actual);
    }

    public function testGet()
    {
        $this->assertNull(Request::get('param'));
        $this->assertSame(111, Request::get('param', 111));

        $_GET['param'] = 'val';
        $this->assertSame('val', Request::get('param'));
    }

    public function testPost()
    {
        $this->assertNull(Request::post('param'));
        $this->assertSame([222], Request::post('param', [222]));

        $_POST['param'] = 'val';
        $this->assertSame('val', Request::post('param'));
    }

    public function testRequest()
    {
        $this->assertNull(Request::request('param'));
        $this->assertSame([444], Request::request('param', [444]));

        $_REQUEST['param'] = 'val';
        $this->assertSame('val', Request::request('param'));
    }

    public function testFiles()
    {
        $_FILES = [
            'Model' => [
                'name' => '...',
                'type' => '...',
                'tmp_name' => '...',
                'error' => '...',
                'size' => '...',
            ],
        ];

        $this->assertSame([], Request::files('notexists'));
        $this->assertSame($_FILES['Model'], Request::files('Model'));
    }
}
