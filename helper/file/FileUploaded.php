<?php

namespace twin\helper\file;

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
    public static function instance(array $properties = []): self
    {
        $path = array_key_exists('tmp_name', $properties) ? $properties['tmp_name'] : '';
        $file = new static($path);

        foreach ($file as $name => $value) {
            if (!array_key_exists($name, $properties)) {
                continue;
            }
            $file->$name = $properties[$name];
        }

        return $file;
    }

    /**
     * Разобрать массив $_FILES и скомпоновать его в виде объектов.
     * @param array $data
     * @return static[]
     */
    public static function parse(array $data): array
    {
        $rearranged = [];

        foreach ($data as $attr => $fields) {
            foreach ($fields as $field => $items) {
                foreach ((array)$items as $i => $item) {
                    $rearranged[$field][$i][$attr] = $item;
                }
            }
        }

        $result = [];

        foreach ($rearranged as $field => $items) {
            foreach ($items as $i => $item) {
                if ($item['error']) continue;
                $result[$field][$i] = static::instance($item);
            }
        }

        return $result;
    }
}
