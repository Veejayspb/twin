<?php

namespace twin\helper\file;

class UploadedFile extends File
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
     * Разобрать массив $_FILES и скомпоновать его в виде объектов.
     * @param array $data
     * @return self[]
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
                $result[$field][$i] = self::instance($item);
            }
        }
        return $result;
    }

    /**
     * Инстанцировать объект и заполнить свойства.
     * @param array $properties - свойства объекта
     * @return self
     */
    private static function instance(array $properties = []): self
    {
        $path = array_key_exists('tmp_name', $properties) ? $properties['tmp_name'] : '';
        $file = new self($path);
        foreach ($file as $name => $value) {
            if (!array_key_exists($name, $properties)) continue;
            $file->$name = $properties[$name];
        }
        return $file;
    }
}
