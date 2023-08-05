<?php

namespace twin\test\unit\helper\template;

use twin\helper\template\Template;
use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;
use twin\test\helper\Temp;

class TemplateTest extends BaseTestCase
{
    public function testConstruct()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'not-exists.tpl';
        $template = new Template($path);
        $proxy = new ObjectProxy($template);

        $this->assertSame($path, $proxy->path);
    }

    public function testSave()
    {
        $temp = new Temp;
        $temp->createFile('test.tpl', '{{first}} - {{second}}');

        $fromPath = $temp->getFilePath('test.tpl');
        $toPath = $temp->getFilePath('test.txt');

        $template = new Template('not-exists');
        $result = $template->save($toPath);

        $this->assertFalse($result);

        $template = new Template($fromPath);
        $result = $template->save($toPath, [
            'first' => 'one',
            'second' => 'two',
        ]);

        $this->assertTrue($result);
        $this->assertFileExists($toPath);
        $this->assertSame('one - two', file_get_contents($toPath));

        unlink($fromPath);
        unlink($toPath);
    }
}
