<?php

use twin\helper\file\FileType;
use twin\test\helper\BaseTestCase;

final class FileTypeTest extends BaseTestCase
{
    public function testGetExtensions()
    {
        $fileType = new FileType;

        $items = [
            'application/xenc+xml' => [
                'xenc',
            ],
            'application/xhtml+xml' => [
                'xhtml',
                'xht',
            ],
            'not/exists' => [],
        ];

        foreach ($items as $mimeType => $extensions) {
            $result = $fileType->getExtensions($mimeType);
            $this->assertSame($extensions, $result);
        }
    }

    public function testGetExtension()
    {
        $fileType = new FileType;

        $items = [
            'application/yang' => 'yang', // Одно соответствующее расширение
            'application/xml' => 'xml', // Несколько соответствующих расширений
            'not/exists' => null, // Нет соответствий
        ];

        foreach ($items as $mime => $extension) {
            $result = $fileType->getExtension($mime);
            $this->assertSame($extension, $result);
        }
    }

    public function testGetMimeTypes()
    {
        $fileType = new FileType;

        $items = [
            'wof' => [
                'application/font-woff',
            ],
            'php' => [
                'application/php',
                'application/x-httpd-php',
                'application/x-httpd-php-source',
                'application/x-php',
                'text/php',
                'text/x-php',
            ],
            'notexists' => [],
        ];

        foreach ($items as $extension => $mimeTypes) {
            $result = $fileType->getMimeTypes($extension);
            $this->assertSame($mimeTypes, $result);
        }
    }

    public function testGetMimeType()
    {
        $fileType = new FileType;

        $items = [
            'wmv' => 'video/x-ms-wmv', // Один соответствующий mime-тип
            'wmz' => 'application/x-ms-wmz', // Несколько соответствующих mime-типов
            'notexists' => null, // Нет соответствий
        ];

        foreach ($items as $extension => $mime) {
            $result = $fileType->getMimeType($extension);
            $this->assertSame($mime, $result);
        }
    }
}
