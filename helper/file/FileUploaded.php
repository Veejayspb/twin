<?php

namespace twin\helper\file;

use twin\helper\ArrayHelper;
use twin\helper\ObjectHelper;
use twin\helper\Request;

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
     * Разобрать массив $_FILES, полученный из формы с моделью.
     * @param string $modelName - название модели: ModelName
     * @return array
     */
    public static function parseModel(string $modelName): array
    {
        $data = Request::files($modelName);
        $keys = ['name', 'type', 'tmp_name', 'error', 'size'];

        if (!ArrayHelper::keysExist($keys, $data, true)) {
            return [];
        }

        $result = [];

        foreach ((array)$data['name'] as $fieldName => $values) {
            foreach ((array)$values as $i => $value) {
                $result[$fieldName][$i] = static::instance([
                    'name' => ((array)$data['name'][$fieldName])[$i] ?? null,
                    'type' => ((array)$data['type'][$fieldName])[$i] ?? null,
                    'tmp_name' => ((array)$data['tmp_name'][$fieldName])[$i] ?? null,
                    'error' => ((array)$data['error'][$fieldName])[$i] ?? null,
                    'size' => ((array)$data['size'][$fieldName])[$i] ?? null,
                ]);
            }
        }

        return $result;
    }
}
