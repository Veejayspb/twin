<?php

namespace twin\test\unit\helper\file;

use twin\helper\file\Dir;
use twin\helper\file\File;
use twin\test\helper\BaseTestCase;
use twin\test\helper\Temp;

final class DirTest extends BaseTestCase
{
    public function testConstruct()
    {
        // Файл
        $code = $this->catchExceptionCode(function () {
            new Dir(__FILE__);
        });

        $this->assertSame(500, $code);

        // Директория
        $code = $this->catchExceptionCode(function () {
            new Dir(__DIR__);
        });

        $this->assertSame(0, $code);

        // Не существует
        $code = $this->catchExceptionCode(function () {
            new Dir('not-exists');
        });

        $this->assertSame(500, $code);
    }

    public function testIsFile()
    {
        $dir = new Dir(__DIR__);
        $this->assertFalse($dir->isFile());
    }

    public function testCopy()
    {
        $temp = new Temp;

        // Копирование раздела в несуществующую директорию (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('not-exists');
        mkdir($pathFrom);
        $dir = new Dir($pathFrom);
        $result = $dir->copy($pathTo, true);

        $this->assertFalse($result);
        $this->assertDirectoryExists($pathFrom);
        $this->assertDirectoryDoesNotExist($pathTo);

        $temp->clear();

        // Копирование раздела в ту же директорию, где она находится (force)
        $pathFrom = $temp->getFilePath('from');
        mkdir($pathFrom);
        $result = $dir->copy(__DIR__, true);

        $this->assertNotFalse($result);

        $temp->clear();

        // Копирование раздела (рекурсивное) в существующую директорию
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        mkdir($temp->getFilePath('from/dir1'));
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copy($pathTo, false);

        $this->assertNotFalse($result);
        $this->assertDirectoryExists($temp->getFilePath('to/from/dir1'));
        $this->assertFileExists($temp->getFilePath('to/from/file.txt'));
        $this->assertDirectoryExists($temp->getFilePath('from/dir1'));
        $this->assertFileExists($temp->getFilePath('from/file.txt'));

        $temp->clear();

        // Копирование раздела в директорию, в которой уже есть одноименный раздел
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        file_put_contents($temp->getFilePath('from/file.txt'), 'new', LOCK_EX);
        mkdir($temp->getFilePath('to/from'));
        file_put_contents($temp->getFilePath('to/from/file.txt'), 'old', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copy($pathTo, false);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('to/from/file.txt'));
        $this->assertFileExists($temp->getFilePath('from/file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('to/from/file.txt'), 'old');
        $this->assertStringMatchesFormatFile($temp->getFilePath('from/file.txt'), 'new');

        $temp->clear();

        // Копирование раздела в директорию, в которой уже есть одноименный раздел (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        file_put_contents($temp->getFilePath('from/file.txt'), 'new', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copy($pathTo, true);

        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('to/from/file.txt'));
        $this->assertFileExists($temp->getFilePath('from/file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('to/from/file.txt'), 'new');
        $this->assertStringMatchesFormatFile($temp->getFilePath('from/file.txt'), 'new');

        $temp->clear();

        // Копирование раздела в директорию, в которой уже есть одноименный файл (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        $dir = new Dir($pathFrom);
        $result = $dir->copy($pathTo, true);

        $this->assertNotFalse($result);
        $this->assertDirectoryExists($pathFrom);

        $temp->clear();
    }

    public function testCopyInner()
    {
        $temp = new Temp;

        // Копирование раздела в несуществующую директорию (force)
        $pathFrom = $temp->getFilePath('from');
        mkdir($temp->getFilePath('from'));
        mkdir($temp->getFilePath('from/dir'));
        $dir = new Dir($pathFrom);
        $result = $dir->copyInner($temp->getFilePath('notexists'), true);

        $this->assertFalse($result);
        $this->assertDirectoryExists($temp->getFilePath('from'));
        $this->assertDirectoryDoesNotExist($temp->getFilePath('notexists'));

        $temp->clear();

        // Копирование раздела в ту же директорию (force)
        $pathFrom = $temp->getFilePath('from');
        mkdir($pathFrom);
        file_put_contents($temp->getFilePath('from/test.txt'), LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copyInner($temp->getFilePath('from'), true);

        $this->assertTrue($result);

        $temp->clear();

        // Копирование раздела в существующую директорию, в которой уже есть одноименные файлы
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        mkdir($temp->getFilePath('to/file.txt'));
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        file_put_contents($temp->getFilePath('from/test.txt'), '', LOCK_EX);
        file_put_contents($temp->getFilePath('to/file.txt/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copyInner($pathTo, false);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('from/file.txt'));
        $this->assertFileExists($temp->getFilePath('to/file.txt/file.txt'));
        $this->assertFileExists($temp->getFilePath('to/test.txt'));

        $temp->clear();

        // Копирование раздела в существующую директорию, в которой уже есть одноименные файлы (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        mkdir($temp->getFilePath('to/file.txt'));
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        file_put_contents($temp->getFilePath('to/file.txt/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->copyInner($pathTo, true);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('from/file.txt'));
        $this->assertFileExists($temp->getFilePath('to/file.txt'));

        $temp->clear();
    }

    public function testMove()
    {
        $temp = new Temp;

        // Перемещение раздела в несуществующую директорию (force)
        $pathFrom = $temp->getFilePath('from');
        mkdir($pathFrom);
        $dir = new Dir($pathFrom);
        $result = $dir->move($temp->getFilePath('notexists'), true);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('from'));
        $this->assertFileDoesNotExist($temp->getFilePath('notexists'));

        $temp->clear();

        // Перемещение раздела в ту же директорию, где она находится (force)
        $pathFrom = $temp->getFilePath('from');
        mkdir($pathFrom);
        $dir = new Dir($pathFrom);
        $result = $dir->move(dirname($pathFrom), true);

        $this->assertTrue($result);
        $this->assertDirectoryExists($temp->getFilePath('from'));

        $temp->clear();

        // Перемещение раздела (рекурсивное) в существующую директорию
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->move($pathTo, false);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('to/from/file.txt'));
        $this->assertDirectoryDoesNotExist($temp->getFilePath('from'));
        $this->assertSame($temp->getFilePath('to' . DIRECTORY_SEPARATOR . 'from'), $dir->getPath());

        $temp->clear();

        // Перемещение раздела в директорию, в которой уже есть одноименный раздел
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        mkdir($temp->getFilePath('to/from'));
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->move($pathTo, false);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist($temp->getFilePath('to/from/file.txt'));
        $this->assertFileExists($temp->getFilePath('from/file.txt'));

        $temp->clear();

        // Перемещение раздела в директорию, в которой уже есть одноименный раздел (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->move($pathTo, true);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('to/from/file.txt'));
        $this->assertFileDoesNotExist($temp->getFilePath('from/file.txt'));

        $temp->clear();

        // Перемещение раздела в директорию, в которой уже есть одноименный файл (force)
        $pathFrom = $temp->getFilePath('from');
        $pathTo = $temp->getFilePath('to');
        mkdir($pathFrom);
        mkdir($pathTo);
        file_put_contents($temp->getFilePath('to/from'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->move($pathTo, true);

        $this->assertTrue($result);
        $this->assertDirectoryExists($temp->getFilePath('to' . DIRECTORY_SEPARATOR . 'from'));
        $this->assertDirectoryDoesNotExist($temp->getFilePath('from'));

        $temp->clear();
    }

    public function testDelete()
    {
        $temp = new Temp;

        // Рекурсивное удаление директории с файлами внутри
        $pathFrom = $temp->getFilePath('from');
        mkdir($pathFrom);
        file_put_contents($temp->getFilePath('from/file.txt'), '', LOCK_EX);
        $dir = new Dir($pathFrom);
        $result = $dir->delete();

        $this->assertTrue($result);
        $this->assertDirectoryDoesNotExist($temp->getFilePath('dir'));
        $this->assertFileDoesNotExist($temp->getFilePath('dir/file.txt'));

        $temp->clear();

        // Удаление уже несуществующей директории
        $result = $dir->delete();

        $this->assertTrue($result);
    }

    public function testGetChildren()
    {
        $temp = new Temp;

        mkdir($temp->getFilePath('dir1'));
        mkdir($temp->getFilePath('dir1/dir2'));
        file_put_contents($temp->getFilePath('dir1/file1'), '', LOCK_EX);
        $dir1 = new Dir($temp->getFilePath('dir1'));
        $dir2 = new Dir($temp->getFilePath('dir1/dir2'));
        $file1 = new File($temp->getFilePath('dir1/file1'));
        $children = $dir1->getChildren();

        $this->assertEquals(2, count($children));
        $this->assertEquals($dir2, $children[0]);
        $this->assertEquals($file1, $children[1]);

        $temp->clear();
    }

    public function testCreateDirectory()
    {
        $temp = new Temp;

        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($temp->getFilePath('dir/test'), '', LOCK_EX);
        $dir = new Dir($pathDir);

        $result = $dir->createDirectory('test', false);
        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/test'));

        $result = $dir->createDirectory('test', true);
        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/test'));

        $temp->clear();
    }

    public function testCreateFile()
    {
        $temp = new Temp;

        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        mkdir($temp->getFilePath('dir/test'));
        $dir = new Dir($pathDir);

        $result = $dir->createFile('test', '', false);
        $this->assertFalse($result);
        $this->assertDirectoryExists($temp->getFilePath('dir/test'));

        $result = $dir->createFile('test', '', true);
        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/test'));

        $temp->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new Temp)->clear();
    }
}
