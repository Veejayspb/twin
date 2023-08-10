<?php

namespace twin\helper\file;

use twin\helper\ObjectHelper;

/**
 * Хелпер для работы с загруженными файлами из массива $_FILES.
 *
 * Class FileUploaded
 */
class FileUploaded extends File
{
    /**
     * Название файла.
     * @var string - text.txt
     */
    public $name;

    /**
     * Тип файла.
     * @var string - text/plain
     */
    public $type;

    /**
     * Код ошибки.
     * @var int
     */
    public $error;

    /**
     * Размер файла в байтах
     * @var int
     */
    public $size;

    /**
     * Инстанцировать объект и заполнить свойства.
     * @param array $properties - свойства объекта
     * @return static
     */
    public static function instance(array $properties): self
    {
        $path = $properties['tmp_name'] ?? '';
        $file = new static($path);
        return ObjectHelper::setProperties($file, $properties);
    }

    /**
     * Разобрать массив $_FILES и скомпоновать его в виде объектов.
     * @param array $data - $_FILES
     * @return array
     */
    public static function parse(array $data): array
    {
        $result = [];

        foreach ($data as $name => $attributes) {
            $result[$name] = self::parseField($attributes);
        }

        return $result;
    }

    /**
     * Разобрать данные конкретного поля массива $_FILES.
     * @param array $attributes - $_FILES['field_name']
     * @return array
     */
    private static function parseField(array $attributes): array
    {
        $rearranged = [];

        foreach ($attributes as $name => $values) {
            foreach ((array)$values as $i => $value) {
                $rearranged[$i][$name] = $value;
            }
        }

        $result = [];

        foreach ($rearranged as $i => $attributes) {
            if ($attributes['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            $result[$i] = static::instance($attributes);
        }

        return $result;
    }
}
