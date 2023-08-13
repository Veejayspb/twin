<?php

use twin\helper\file\File;
use twin\test\helper\BaseTestCase;
use twin\test\helper\Temp;

final class FileTest extends BaseTestCase
{
    public function testConstruct()
    {
        // Файл
        $code = $this->catchExceptionCode(function () {
            new File(__FILE__);
        });

        $this->assertSame(0, $code);

        // Директория
        $code = $this->catchExceptionCode(function () {
            new File(__DIR__);
        });

        $this->assertSame(500, $code);

        // Не существует
        $code = $this->catchExceptionCode(function () {
            new File('not-exists');
        });

        $this->assertSame(500, $code);
    }

    public function testIsFile()
    {
        $dir = new File(__FILE__);
        $this->assertTrue($dir->isFile());
    }

    public function testCopy()
    {
        $temp = new Temp;

        // Копирование файла в несуществующую директорию (force)
        $path = $temp->getFilePath('file.txt');
        file_put_contents($path, '', LOCK_EX);
        $file = new File($path);
        $result = $file->copy($temp->getFilePath('notexists'), true);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist($temp->getFilePath('notexists/file.txt'));

        $temp->clear();

        // Копирование файла в ту же директорию, в которой он находится (force)
        $path = $temp->getFilePath('file.txt');
        file_put_contents($path, '', LOCK_EX);
        $file = new File($path);
        $result = $file->copy(dirname($path), true);

        $this->assertNotFalse($result);
        $this->assertFileExists($path);

        $temp->clear();

        // Копирование файла в существующую директорию
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->copy($pathDir, false);

        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));

        $temp->clear();

        // Копирование файла в директорию, в которой уже есть такой файл
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, 'new', LOCK_EX);
        file_put_contents($temp->getFilePath('dir/file.txt'), 'old', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->copy($pathDir, false);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('dir/file.txt'), 'old');
        $this->assertStringMatchesFormatFile($temp->getFilePath('file.txt'), 'new');

        $temp->clear();

        // Копирование файла в директорию, в которой уже есть такой файл (force)
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, 'new', LOCK_EX);
        file_put_contents($temp->getFilePath('dir/file.txt'), 'old', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->copy($pathDir, true);

        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('dir/file.txt'), 'new');
        $this->assertStringMatchesFormatFile($temp->getFilePath('file.txt'), 'new');

        $temp->clear();

        // Копирование файла в директорию, в которой уже есть одноименная директория
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        mkdir($temp->getFilePath('dir/file.txt'));
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->copy($pathDir, false);

        $this->assertFalse($result);
        $this->assertDirectoryExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));

        $temp->clear();

        // Копирование файла в директорию, в которой уже есть одноименная директория (force)
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        mkdir($temp->getFilePath('dir/file.txt'));
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->copy($pathDir, true);

        $this->assertNotFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));

        $temp->clear();
    }

    public function testMove()
    {
        $temp = new Temp;

        // Перемещение файла в несуществующую директорию (force)
        $pathFile = $temp->getFilePath('file.txt');
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($temp->getFilePath('notexists'), true);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('file.txt'));

        $temp->clear();

        // Перемещение файла в ту же директорию, в которой он находится
        $pathFile = $temp->getFilePath('file.txt');
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move(dirname($pathFile), true);

        $this->assertTrue($result);
        $this->assertFileExists($pathFile);

        $temp->clear();

        // Перемещение файла в существующую директорию
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($pathDir, false);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileDoesNotExist($temp->getFilePath('file.txt'));
        $this->assertEquals($temp->getFilePath('dir' . DIRECTORY_SEPARATOR . 'file.txt'), $file->getPath());

        $temp->clear();

        // Перемещение файла в директорию, в которой уже есть такой файл
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, 'new', LOCK_EX);
        file_put_contents($temp->getFilePath('dir/file.txt'), 'old', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($pathDir, false);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileExists($temp->getFilePath('file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('dir/file.txt'), 'old');
        $this->assertStringMatchesFormatFile($temp->getFilePath('file.txt'), 'new');

        $temp->clear();

        // Перемещение файла в директорию, в которой уже есть такой файл (force)
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        file_put_contents($pathFile, 'new', LOCK_EX);
        file_put_contents($temp->getFilePath('dir/file.txt'), 'old', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($pathDir, true);

        $this->assertTrue($result);
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));
        $this->assertFileDoesNotExist($temp->getFilePath('file.txt'));
        $this->assertStringMatchesFormatFile($temp->getFilePath('dir/file.txt'), 'new');

        $temp->clear();

        // Перемещение файла в директорию, в которой уже есть одноименная директория
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        mkdir($temp->getFilePath('dir/file.txt'));
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($pathDir, false);

        $this->assertFalse($result);
        $this->assertFileExists($temp->getFilePath('file.txt'));
        $this->assertDirectoryExists($temp->getFilePath('dir/file.txt'));

        $temp->clear();

        // Перемещение файла в директорию, в которой уже есть одноименная директория (force)
        $pathFile = $temp->getFilePath('file.txt');
        $pathDir = $temp->getFilePath('dir');
        mkdir($pathDir);
        mkdir($temp->getFilePath('dir/file.txt'));
        file_put_contents($pathFile, '', LOCK_EX);
        $file = new File($pathFile);
        $result = $file->move($pathDir, true);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($temp->getFilePath('file.txt'));
        $this->assertFileExists($temp->getFilePath('dir/file.txt'));

        $temp->clear();
    }

    public function testDelete()
    {
        $temp = new Temp;

        // Удаление существующего файла
        $path = $temp->getFilePath('file.txt');
        file_put_contents($path, '', LOCK_EX);
        $file = new File($path);
        $result = $file->delete();

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);

        $temp->clear();

        // Удаление несуществующего файла
        $result = $file->delete();

        $this->assertTrue($result);
    }

    public function testGetContent()
    {
        $temp = new Temp;

        // Существующий файл
        $path = $temp->getFilePath('file.txt');
        file_put_contents($path, 'data', LOCK_EX);
        $file = new File($path);
        $content = $file->getContent();

        $this->assertSame('data', $content);

        $temp->clear();

        // Несуществующий файл
        $content = $file->getContent();

        $this->assertFalse($content);
    }

    public function testGetMimeType()
    {
        $temp = new Temp;

        // TXT
        $path = $temp->getFilePath('file.txt');
        file_put_contents($path, 'text', LOCK_EX);
        $file = new File($path);

        $this->assertSame('text/plain', $file->getMimeType());

        // XML
        $path = $temp->getFilePath('file.xml');
        file_put_contents($path, '<?xml version="1.0" encoding="UTF-8"?>', LOCK_EX);
        $file = new File($path);

        $this->assertSame('text/xml', $file->getMimeType());

        $temp->clear();

        // Файл уже удален
        $mime = $file->getMimeType();
        $this->assertNull($mime);
    }

    public function testGetExtension()
    {
        $temp = new Temp;

        $items = [
            [
                'name' => 'test.txt',
                'mime' => 'application/xml',
                'result' => 'txt',
            ],
            [
                'name' => 'test',
                'mime' => 'application/json',
                'result' => 'json',
            ],
            [
                'name' => 'test',
                'mime' => null,
                'result' => null,
            ],
        ];

        foreach ($items as $item) {
            $path = $temp->getFilePath($item['name']);
            file_put_contents($path, '', LOCK_EX);

            $file = $this->getMockBuilder(File::class)
                ->setConstructorArgs([$path])
                ->onlyMethods(['getMimeType'])
                ->getMock();

            $file
                ->expects($this->any())
                ->method('getMimeType')
                ->willReturn($item['mime']);

            $result = $file->getExtension();

            $this->assertSame($item['result'], $result);
        }

        $temp->clear();
    }

    public function testGetExtFromName()
    {
        $temp = new Temp;

        $pairs = [
            'test.txt' => 'txt',
            'test.name.xml' => 'xml',
            'test' => null,
        ];

        foreach ($pairs as $name => $ext) {
            $path = $temp->getFilePath($name);
            file_put_contents($path, '', LOCK_EX);
            $file = new File($path);
            $this->assertSame($ext, $file->getExtFromName());
        }

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
