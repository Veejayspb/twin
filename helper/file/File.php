<?php

namespace twin\helper\file;

use twin\common\Exception;

/**
 * Хелпер для манипуляции с файлами.
 *
 * Class File
 *
 * @todo: remove exif and other meta-info
 */
class File extends FileCommon
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        if (!is_file($path)) {
            throw new Exception(500, "Is not a file: $path");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFile(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $path, bool $force = false)
    {
        if (
            !file_exists($this->path) ||
            !is_dir($path)
        ) {
            return false;
        }

        $newPath = $this->normalizePath($path . DIRECTORY_SEPARATOR . basename($this->path));
        $exists = file_exists($newPath);

        // Если копирование в ту же директорию, где находится файл
        if ($newPath == $this->path) {
            return new static($newPath);
        }

        if ($exists && !$force) {
            return false;
        }

        $result = @copy($this->path, $newPath);

        if (!$result) {
            return false;
        }

        return new static($newPath);
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $path, bool $force = false): bool
    {
        $path = $this->normalizePath($path);

        // Если перемещение в ту же директорию, где находится файл
        if ($path == dirname($this->path)) {
            return true;
        }

        $result = $this->copy($path, $force);

        if ($result && $this->delete()) {
            $this->path = $path . DIRECTORY_SEPARATOR . $this->getName();
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if (is_file($this->path)) {
            return unlink($this->path);
        }

        return true;
    }

    /**
     * Вернуть содержимое файла.
     * @return string|bool
     */
    public function getContent()
    {
        return @file_get_contents($this->path);
    }

    /**
     * Вернуть MIME-тип файла.
     * @return string|bool
     */
    public function getMimeType()
    {
        return @mime_content_type($this->path);
    }

    /**
     * Определить расширение файла.
     * @return string|bool
     */
    public function getExtension()
    {
        // Извлечь из названия
        $ext = $this->getExtensionFromName();
        if ($ext) {
            return $ext;
        }

        // Иначе определить по mime-type
        $mime = $this->getMimeType();
        if (!$mime) {
            return false;
        }

        $ext = FileType::getExtension($mime);
        return $ext ?: false;
    }

    /**
     * Извлечь расширение файла из названия.
     * @return string|bool
     */
    protected function getExtensionFromName()
    {
        preg_match('/\.(.+)$/', $this->getName(), $matches);
        return $matches ? $matches[1] : false;
    }
}
