<?php

use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use twin\view\View;

final class ViewTest extends BaseTestCase
{
    const CSS = 'css';
    const JS = 'js';
    const HEAD = 'head';
    const BODY = 'body';

    public function testRender()
    {
        $items = [
            'simple' => 0,
            'not/exists' => 500,
        ];

        $view = $this->getView();

        foreach ($items as $relAlias => $expectedCode) {
            $content = null;

            $code = $this->catchExceptionCode(function () use ($view, $relAlias, &$content) {
                $content = $view->render($relAlias, ['content' => 'text']);
            });

            $this->assertSame($expectedCode, $code);

            if ($code == 0) {
                $this->assertSame(PHP_EOL . 'text', $content);
            }
        }
    }

    public function testRenderLayout()
    {
        $view = $this->getView();
        $view->addHead(self::HEAD);
        $view->addBody(self::BODY);

        // Body JS
        $view->scriptBody = true;

        $result = $view->renderLayout('simple', [
            'content' => 'text',
        ]);

        $actual = str_replace([PHP_EOL, "\t"], '', $result);
        $expected = implode('', [
            self::CSS,
            self::HEAD,
            'text',
            self::JS,
            self::BODY,
        ]);

        $this->assertSame($expected, $actual);

        // Head JS
        $view->scriptBody = false;

        $result = $view->renderLayout('simple', [
            'content' => 'text',
        ]);

        $actual = str_replace([PHP_EOL, "\t"], '', $result);
        $expected= implode('', [
            self::CSS,
            self::JS,
            self::HEAD,
            'text',
            self::BODY,
        ]);

        $this->assertSame($expected, $actual);
    }

    public function testBeginEnd()
    {
        $view = $this->getView();

        $result = $view->begin();
        $this->assertTrue($result);

        echo 'inner';

        $result = $view->end('@test/helper/view/simple.php');
        $this->assertSame(PHP_EOL . 'inner', $result);
    }

    public function testAddHead()
    {
        $items = [
            'string',
            ' ',
            '',
        ];

        $view = $this->getView();
        $head = [];

        foreach ($items as $item) {
            $head[] = $item;
            $view->addHead($item);

            $proxy = new ObjectProxy($view);
            $this->assertSame($head, $proxy->head);
        }
    }

    public function testAddBody()
    {
        $items = [
            'string',
            ' ',
            '',
        ];

        $view = $this->getView();
        $body = [];

        foreach ($items as $item) {
            $body[] = $item;
            $view->addBody($item);

            $proxy = new ObjectProxy($view);
            $this->assertSame($body, $proxy->body);
        }
    }

    /**
     * @return View
     */
    protected function getView(): View
    {
        $properties = [
            'alias' => '@test/helper/view',
            'layoutPath' => '@test/helper/view/template.php',
        ];

        return $this->mock(View::class, null, [$properties], [
            'getCss' => [self::CSS],
            'getJs' => [self::JS],
        ]);
    }
}
