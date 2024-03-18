<?php

use twin\helper\template\Template;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use test\helper\Temp;

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
        // Исходный шаблон не сущ-ет
        $temp = new Temp;
        $temp->createFile('test.tpl', '{{first}} - {{second}}');

        $fromPath = $temp->getFilePath('test.tpl');
        $toPath = $temp->getFilePath('test.txt');

        $template = new Template('not-exists');
        $result = $template->save($toPath);

        $this->assertFalse($result);

        // В названии файла присутствуют недопустимые символы
        $template = new Template($fromPath);

        $result = $template->save($temp->getFilePath('te|st.txt'));

        $this->assertFalse($result);

        // Корректное название файла
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
