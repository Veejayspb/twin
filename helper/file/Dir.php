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
        $path = $this->normalizePath($path);

        if (!file_exists($this->path)) {
            return false;
        }

        // Если целевая директория не сущ-ет или в ней уже сущ-ет одноименный файл, то раздел скопировать не получится
        if (!is_dir($path)) {
            return false;
        }

        $exists = is_dir($path . DIRECTORY_SEPARATOR . $this->getName());

        if (!$force && $exists) {
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

        return @rmdir($this->path);
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
     * Добавить вложенную директорию.
     * Создать вложенную директорию.
     * @param string $name - название директории
     * @param bool $force - удалить одноименный файл (если он имеется)
     * @return static|false
     * @throws Exception
     */
    protected function createDirectory(string $name, bool $force)
    {
        $path = $this->path . DIRECTORY_SEPARATOR . $name;

        // Если директория уже существует
        if (is_dir($path)) {
            return new static($path);
        }

        // Если существует одноименный файл, то директорию создать не получится
        if (is_file($path)) {
            if (!$force) {
                return false;
            }

            // FORCE-режим: если файл мешает созданию одноименной директории, то удаляем его
            if (!unlink($path)) {
                return false;
            }
        }

        // Попытка создания директории
        if (!@mkdir($path)) {
            return false;
        }

        return new static($path);
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
