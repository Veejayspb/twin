<?php

namespace twin\test\unit\helper\file;

use twin\helper\file\AbstractFile;
use twin\helper\file\Dir;
use twin\test\helper\BaseTestCase;
use twin\test\helper\ObjectProxy;

class AbstractFileTest extends BaseTestCase
{
    public function testConstruct()
    {
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [__FILE__]);
        $proxy = new ObjectProxy($mock);
        $this->assertSame(__FILE__, $proxy->path);

        $code = $this->catchExceptionCode(function () {
            $this->getMockForAbstractClass(AbstractFile::class, ['not-exists']);
        });

        $this->assertSame(500, $code);
    }

    public function testGetPath()
    {
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [__FILE__]);
        $this->assertSame(__FILE__ , $mock->getPath());
    }

    public function testGetName()
    {
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [__FILE__]);
        $this->assertSame('AbstractFileTest.php', $mock->getName());
    }

    public function testRename()
    {
        // Файл
        $pathOriginal = dirname(__DIR__, 3) . '/temp/test.txt';
        $pathRenamed = dirname(__DIR__, 3) . '/temp/renamed.txt';

        file_put_contents($pathOriginal, '', LOCK_EX);
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [$pathOriginal]);
        $result = $mock->rename('renamed.txt');
        $this->assertTrue($result);
        $this->assertFileExists($pathRenamed);
        unlink($pathRenamed);

        // Директория
        $pathOriginal = dirname(__DIR__, 3) . '/temp/test';
        $pathRenamed = dirname(__DIR__, 3) . '/temp/renamed';

        mkdir($pathOriginal);
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [$pathOriginal]);
        $result = $mock->rename('renamed');
        $this->assertTrue($result);
        $this->assertFileExists($pathRenamed);
        rmdir($pathRenamed);
    }

    public function testParent()
    {
        // Файл
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [__FILE__]);
        $parent = $mock->getParent();
        $proxy = new ObjectProxy($parent);

        $this->assertTrue(get_class($parent) == Dir::class);
        $this->assertSame(__DIR__ , $proxy->path);

        // Директория
        $mock = $this->getMockForAbstractClass(AbstractFile::class, [__DIR__]);
        $parent = $mock->getParent();
        $proxy = new ObjectProxy($parent);

        $this->assertTrue(get_class($parent) == Dir::class);
        $this->assertSame(dirname(__DIR__) , $proxy->path);
    }
}
