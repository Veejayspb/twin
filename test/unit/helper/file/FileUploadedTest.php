<?php

namespace unit\helper\file;

use twin\helper\file\FileUploaded;
use twin\test\helper\BaseTestCase;
use twin\test\helper\Temp;

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

    public function testParse()
    {
        $temp = new Temp;
        $data = $this->getFilesData();
        $files = FileUploaded::parse($data);

        $expected = new FileUploaded($data['single']['tmp_name']);
        $expected->name = 'image.jpg';
        $expected->type = 'image/jpeg';
        $expected->error = UPLOAD_ERR_OK;
        $expected->size = 5029932;

        $this->assertEquals($expected, $files['single'][0]);

        $expected = new FileUploaded($data['multiple']['tmp_name'][0]);
        $expected->name = 'text1.txt';
        $expected->type = 'text/plain';
        $expected->error = UPLOAD_ERR_OK;
        $expected->size = 2970;

        $this->assertEquals($expected, $files['multiple'][0]);

        $this->assertCount(1, $files['multiple']);

        $temp->clear();
    }

    /**
     * Сформировать массив $_FILES.
     * @return array
     */
    protected function getFilesData(): array
    {
        $temp = new Temp;

        $image = $temp->getFilePath('php8D87.tmp');
        $text1 = $temp->getFilePath('php8E43.tmp');
        $text2 = $temp->getFilePath('php8E54.tmp');

        file_put_contents($image, '', LOCK_EX);
        file_put_contents($text1, '', LOCK_EX);
        file_put_contents($text2, '', LOCK_EX);

        return [
            'single' => [
                'name' => 'image.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $image,
                'error' => UPLOAD_ERR_OK,
                'size' => 5029932,
            ],
            'multiple' => [
                'name' => [
                    'text1.txt',
                    'text2.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/plain',
                ],
                'tmp_name' => [
                    $text1,
                    $text2,
                ],
                'error' => [
                    UPLOAD_ERR_OK,
                    UPLOAD_ERR_INI_SIZE,
                ],
                'size' => [
                    2970,
                    43105,
                ],
            ],
        ];
    }
}
