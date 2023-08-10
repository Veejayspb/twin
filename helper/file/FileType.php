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
class FileType
{
    /**
     * Данные по расширениям и mime-типам.
     * @var array
     */
    protected static $data;

    /**
     * Вернуть все расширения, соответствующие указанному mime-типу.
     * @param string $mimeType
     * @return array
     */
    public function getExtensions(string $mimeType): array
    {
        $data = $this->getData();
        return empty($data['extension'][$mimeType]) ? [] : $data['extension'][$mimeType];
    }

    /**
     * Вернуть первое расширение, соответствующее указанному mime-типу.
     * @param string $mimeType
     * @return string|null
     */
    public function getExtension(string $mimeType): ?string
    {
        $extensions = $this->getExtensions($mimeType);
        return empty($extensions) ? null : current($extensions);
    }

    /**
     * Вернуть все mime-типы, соответствующие указанному расширению.
     * @param string $extension
     * @return array
     */
    public function getMimeTypes(string $extension): array
    {
        $data = $this->getData();
        return empty($data['mime'][$extension]) ? [] : $data['mime'][$extension];
    }

    /**
     * Вернуть первый mime-тип, соответствующий указанному расширению.
     * @param string $extension
     * @return string|null
     */
    public function getMimeType(string $extension): ?string
    {
        $mimeTypes = $this->getMimeTypes($extension);
        return empty($mimeTypes) ? null : current($mimeTypes);
    }

    /**
     * Массив с данными по расширениям и mime-типам.
     * @return array
     */
    protected function getData(): array
    {
        if (self::$data === null) {
            self::$data = Twin::import('@twin/config/mime-types.php') ?: [];
        }

        return self::$data;
    }
}
