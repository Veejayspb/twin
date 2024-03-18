<?php

use twin\helper\file\FileUploaded;
use test\helper\BaseTestCase;

final class FileUploadedTest extends BaseTestCase
{
    const DATA_EXISTS = [
        'name' => 'FileUploadedTest.php',
        'type' => 'application/php',
        'tmp_name' => __FILE__,
        'error' => UPLOAD_ERR_OK,
        'size' => 5029932,
    ];

    const DATA_NOT_EXISTS = [
        'name' => 'notexists.html',
        'type' => 'text/html',
        'tmp_name' => 'notexists',
        'error' => UPLOAD_ERR_OK,
        'size' => 4927,
    ];

    public function testInstance()
    {
        // Существующий файл
        $actual = FileUploaded::instance(self::DATA_EXISTS);

        $expected = new FileUploaded(self::DATA_EXISTS['tmp_name']);
        $expected->name = self::DATA_EXISTS['name'];
        $expected->type = self::DATA_EXISTS['type'];
        $expected->error = self::DATA_EXISTS['error'];
        $expected->size = self::DATA_EXISTS['size'];

        $this->assertEquals($expected, $actual);

        // Существующий файл с неполным набором данных
        $actual = FileUploaded::instance([
            'tmp_name' => __FILE__,
            'name' => false,
            'type' => [],
        ]);

        $expected = new FileUploaded(__FILE__);
        $expected->name = false;
        $expected->type = [];

        $this->assertEquals($expected, $actual);

        // Несуществующий файл
        $code = $this->catchExceptionCode(function () {
            FileUploaded::instance(self::DATA_NOT_EXISTS);
        });

        $this->assertSame(500, $code);

        // Отсутствует атрибут с путем до файла
        $code = $this->catchExceptionCode(function () {
            $properties = self::DATA_EXISTS;
            unset($properties['tmp_name']);
            FileUploaded::instance($properties);
        });

        $this->assertSame(500, $code);
    }

    public function testParseModel()
    {
        $_FILES = $this->getFilesData();

        $file1 = new FileUploaded(__FILE__);
        $file1->name = 'image.jpg';
        $file1->type = 'image/jpeg';
        $file1->error = UPLOAD_ERR_OK;
        $file1->size = 2222;

        $file2 = new FileUploaded(__FILE__);
        $file2->name = 'text1.txt';
        $file2->type = 'text/plain';
        $file2->error = UPLOAD_ERR_OK;
        $file2->size = 1111;

        $file3 = new FileUploaded(__FILE__);
        $file3->name = 'text2.txt';
        $file3->type = 'text/plain';
        $file3->error = UPLOAD_ERR_INI_SIZE;
        $file3->size = 99999;

        $expected = [
            'field_1' => [$file1],
            'field_2' => [$file2, $file3],
        ];

        $this->assertEquals([], FileUploaded::parseModel('NotExists'));
        $this->assertEquals($expected, FileUploaded::parseModel('ModelName'));

        unset($_FILES['ModelName']['name']);
        $this->assertEquals([], FileUploaded::parseModel('ModelName'));
    }

    /**
     * Сформировать массив $_FILES.
     * @return array
     */
    protected function getFilesData(): array
    {
        return [
            'ModelName' => [
                'name' => [
                    'field_1' => 'image.jpg',
                    'field_2' => ['text1.txt', 'text2.txt'],
                ],
                'type' => [
                    'field_1' => 'image/jpeg',
                    'field_2' => ['text/plain', 'text/plain'],
                ],
                'tmp_name' => [
                    'field_1' => __FILE__,
                    'field_2' => [__FILE__, __FILE__],
                ],
                'error' => [
                    'field_1' => UPLOAD_ERR_OK,
                    'field_2' => [UPLOAD_ERR_OK, UPLOAD_ERR_INI_SIZE],
                ],
                'size' => [
                    'field_1' => 2222,
                    'field_2' => [1111, 99999],
                ],
            ],
        ];
    }
}
