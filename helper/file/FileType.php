<?php

namespace twin\helper\file;

use twin\Twin;

/**
 * Хелпер для сопоставления mime-type и расширения файла.
 *
 * Class FileType
 *
 * @link http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
 */
final class FileType
{
    /**
     * Данные по расширениям и mime-типам.
     * @var array
     */
    protected static $data;

    /**
     * Определеить расширение по mime-типу.
     * @param string $mimeType
     * @return string|null
     */
    public static function getExtension(string $mimeType)
    {
        $data = self::getData();

        if (isset($data['extension'][$mimeType])) {
            return $data['extension'][$mimeType][0];
        }

        return null;
    }

    /**
     * Определить mime-тип по расширению.
     * @param string $extension
     * @return string|null
     */
    public static function getMimeType(string $extension)
    {
        $data = self::getData();

        if (isset($data['mime'][$extension])) {
            return $data['mime'][$extension][0];
        }

        return null;
    }

    /**
     * Массив с данными по расширениям и mime-типам.
     * @return array
     */
    protected static function getData(): array
    {
        if (self::$data === null) {
            $path = Twin::getAlias('@twin/config/mime-types.php');
            self::$data = is_file($path) ? require $path : [];
        }

        return self::$data;
    }
}
