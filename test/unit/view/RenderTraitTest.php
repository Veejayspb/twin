<?php

use test\helper\BaseTestCase;
use twin\view\RenderTrait;

class RenderTraitTest extends BaseTestCase
{
    public function testRenderPath()
    {
        $mock = $this->mock(RenderTrait::class); /* @var RenderTrait $mock */

        $code = $this->catchExceptionCode(function () use ($mock) {
            $mock->renderPath('@test/notexists');
        });

        $this->assertSame(500, $code);

        $content = null;

        $code = $this->catchExceptionCode(function () use ($mock, &$content) {
            $content = $mock->renderPath('@test/helper/view/simple.php', ['content' => 'text']);
        });

        $this->assertSame(PHP_EOL . 'text', $content);
        $this->assertSame(0, $code);
    }
}
