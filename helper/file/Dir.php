<?php

namespace twin\helper\file;

use DirectoryIterator;
use twin\common\Exception;

/**
 * Хелпер для манипуляции с директориями.
 *
 * Class Dir
 */
class Dir extends FileCommon
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
     */
    public function copy(string $path, bool $force = false)
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

        $this->copyInner($dir, $force);
        return $to;
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

        foreach ($items as $item) { /* @var DirectoryIterator $item */

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
     * @return self|false
     */
    public function createDirectory(string $name, bool $force = false)
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

            // FORCE-режим: если файл мешает созданию одноименной директории, то удаляем его
            if (!unlink($path)) {
                return false;
            }
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
    public function createFile(string $name, string $content, bool $force = false)
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
            if (!rmdir($path)) {
                return false;
            }
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            return false;
        }

        return new File($path);
    }

    /**
     * Скопировать внутренние файлы в текущей директории.
     * @param self $dir - объект с целевой директорией
     * @param bool $force - перезапись существующих файлов
     * @return void
     */
    private function copyInner(self $dir, bool $force)
    {
        $children = $this->getChildren();

        foreach ($children as $child) {
            if ($child->isFile()) {
                $child->copy($dir->getPath(), $force);
            } else {
                $name = $child->getName();
                $innerDir = $dir->createDirectory($name, $force);

                if ($innerDir) {
                    $child->copyInner($innerDir, $force);
                }
            }
        }
    }
}
