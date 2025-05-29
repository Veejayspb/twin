<?php

namespace twin\helper\file;

use DirectoryIterator;
use twin\common\Exception;

/**
 * Хелпер для манипуляции с директориями.
 *
 * Class Dir
 */
class Dir extends AbstractFile
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        if (!is_dir($path)) {
            throw new Exception(500, "Is not a directory: $path");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFile(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @return static|bool
     */
    public function copy(string $path, bool $force = false): bool|static
    {
        if (!file_exists($this->path)) {
            return false;
        }

        $path = $this->normalizePath($path);

        // Если целевая директория не сущ-ет или в ней уже сущ-ет одноименный файл, то раздел скопировать не получится
        if (!is_dir($path)) {
            return false;
        }

        $newPath = $path . DIRECTORY_SEPARATOR . $this->getName();

        // Если целевая директория уже сущ-ет, но вызов без FORCE, то не заменяем ее
        if (!$force && is_dir($newPath)) {
            return false;
        }

        $to = new static($path);
        $dir = $to->createDirectory($this->getName(), $force);

        if (!$dir) {
            return false;
        }

        $this->copyInner($dir->getPath(), $force);
        return $to;
    }

    /**
     * Скопировать внутренние файлы в текущей директории.
     * @param string $path - путь до директории
     * @param bool $force - перезапись существующих файлов
     * @return bool
     */
    public function copyInner(string $path, bool $force = false): bool
    {
        $path = $this->normalizePath($path);

        // Если целевая директория не сущ-ет (или вместо директории - одноименный файл), то раздел скопировать не получится
        if (!is_dir($path)) {
            return false;
        }

        // Если копирование происходит в ту же директорию
        if ($path == $this->path) {
            return true;
        }

        $children = $this->getChildren();
        $result = true;

        foreach ($children as $child) {
            if ($child->isFile()) {
                $result = $child->copy($path, $force) ? $result : false;
            } else {
                $name = $child->getName();
                $dir = new static($path);
                $innerDir = $dir->createDirectory($name, $force);

                if ($innerDir) {
                    $result = $child->copyInner($innerDir->getPath(), $force) ? $result : false;
                }
            }
        }

        // Без FORCE-режима всегда вернется TRUE.
        // В FORCE-режиме вернется TRUE только в том случае, если всё скопировано успешно.
        return !$force || $result;
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $path, bool $force = false): bool
    {
        $path = $this->normalizePath($path);

        if ($path == dirname($this->path)) {
            return true;
        }

        if (!$this->copy($path, $force)) {
            return false;
        }

        if (!$this->delete()) {
            return false;
        }

        $this->path = $path . DIRECTORY_SEPARATOR . $this->getName();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if (!is_dir($this->path)) {
            return true;
        }

        $children = $this->getChildren();

        foreach ($children as $child) {
            $child->delete();
        }

        return rmdir($this->path);
    }

    /**
     * Дочерние файлы/директории.
     * @return File[]|Dir[]
     */
    public function getChildren(): array
    {
        if (!file_exists($this->path)) {
            return [];
        }

        $result = [];
        $items = new DirectoryIterator($this->path);

        foreach ($items as $item) {

            if ($item->isDot()) continue;

            $path = $item->getPathname();

            if (is_file($path)) {
                $result[] = new File($path);
            } else {
                $result[] = new Dir($path);
            }
        }

        return $result;
    }

    /**
     * Создать вложенную директорию.
     * @param string $name - название директории
     * @param bool $force - удалить одноименный файл (если сущ-ет)
     * @return static|false
     */
    public function createDirectory(string $name, bool $force = false): bool|static
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name;

        // Если директория уже существует
        if (is_dir($path)) {
            return new static($path);
        }

        // Если существует одноименный файл
        if (is_file($path)) {
            if (!$force) {
                return false;
            }

            // FORCE-режим: удаление всего, что мешает создать директорию
            $this->deleteIfExists($path);
        }

        if (!mkdir($path)) {
            return false;
        }

        return new static($path);
    }

    /**
     * Создать вложенный файл.
     * @param string $name - название файла
     * @param bool $force - перезаписать одноименную директорию/файл (если сущ-ет)
     * @return File|false
     */
    public function createFile(string $name, string $content, bool $force = false): bool|File
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name;

        if (is_file($path) && !$force) {
            return false;
        }

        // Если существует одноименная директория
        if (is_dir($path)) {
            if (!$force) {
                return false;
            }

            // FORCE-режим: если директория мешает созданию одноименного файла, то удаляем ее
            $dir = new static($path);

            if (!$dir->delete()) {
                return false;
            }
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            return false;
        }

        return new File($path);
    }
}
