<?php

namespace twin\helper\file;

use twin\common\Exception;
use twin\helper\StringHelper;

/**
 * Хелпер для манипуляции с файлами.
 *
 * Class File
 *
 * @todo: remove exif and other meta-info
 */
class File extends AbstractFile
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

        // Если копирование в ту же директорию, где находится файл
        if ($newPath == $this->path) {
            return new static($newPath);
        }

        if (file_exists($newPath)) {
            if (!$force) {
                return false;
            }

            // FORCE-режим: удаление всего, что мешает скопировать файл
            $this->deleteIfExists($newPath);
        }

        $result = copy($this->path, $newPath);

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
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        $mime = @mime_content_type($this->path);
        return $mime ?: null;
    }

    /**
     * Определить расширение файла.
     * @return string|null
     */
    public function getExtension(): ?string
    {
        // Извлечь из названия
        $ext = $this->getExtFromName();
        if ($ext) {
            return $ext;
        }

        // Иначе определить по mime-type
        $mime = $this->getMimeType();
        if (!$mime) {
            return null;
        }

        $ext = (new FileType)->getExtension($mime);
        return $ext ?: null;
    }

    /**
     * Извлечь расширение файла из названия.
     * @return string|null
     */
    public function getExtFromName(): ?string
    {
        $name = $this->getName();
        return StringHelper::getExtFromName($name);
    }
}
