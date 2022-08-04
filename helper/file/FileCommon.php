<?php

namespace twin\helper\file;

use twin\common\Exception;

/**
 * Хелпер для манипуляции с файловой системой.
 *
 * Class FileCommon
 */
abstract class FileCommon
{
    /**
     * Путь до файла/директории.
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     * @throws Exception
     */
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new Exception(500, "File does not exists: $path");
        }

        $this->path = $this->normalizePath($path);
    }

    /**
     * Путь до файла/директории.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Название файла.
     * @return string
     */
    public function getName(): string
    {
        $path = $this->getPath();
        return basename($path);
    }

    /**
     * Переименовать.
     * @param string $name - новое название
     * @return bool
     */
    public function rename(string $name): bool
    {
        if (!file_exists($this->path)) {
            return false;
        }

        $dir = dirname($this->path);
        $newPath = $this->normalizePath($dir . DIRECTORY_SEPARATOR . basename($name));

        if ($newPath == $this->path) {
            return true;
        }

        if (file_exists($newPath)) {
            return false;
        }

        $result = @rename($this->path, $newPath);

        if (!$result) {
            return false;
        }

        $this->path = $newPath;
        return true;
    }

    /**
     * Родительская директория.
     * @return Dir
     */
    public function getParent(): self
    {
        $path = dirname($this->path);
        return new Dir($path);
    }

    /**
     * Является ли файлом.
     * @return bool
     */
    abstract public function isFile(): bool;

    /**
     * Скопировать.
     * @param string $path - путь до директории
     * @param bool $force - перезапись уже существующих файлов
     * @return static|bool
     */
    abstract public function copy(string $path, bool $force = false);

    /**
     * Перенести.
     * Без принудительного перемещения файлы, которые не удалось переместить останутся в исходной директории.
     * @param string $path - путь до директории
     * @param bool $force - перезапись уже существующих файлов
     * @return bool
     */
    abstract public function move(string $path, bool $force = false): bool;

    /**
     * Удалить.
     * @return bool
     */
    abstract public function delete(): bool;

    /**
     * Рекурсивно удалить директорию/файл, если существуют.
     * @param string $path
     * @return bool
     */
    protected function deleteIfExists(string $path): bool
    {
        if (is_file($path)) {
            $file = new File($path);
            return $file->delete();
        }

        if (is_dir($path)) {
            $dir = new Dir($path);
            return $dir->delete();
        }

        return true; // Ни файла, ни директории не сущ-ет
    }

    /**
     * Нормализация пути.
     * @param string $path
     * @return string
     */
    protected function normalizePath(string $path): string
    {
        $dir = dirname($path);
        $name = basename($path);

        // Удаление дублирующихся разделителей и приведение их к стандарту
        $dir = preg_replace('/[\\/]+/', DIRECTORY_SEPARATOR, $dir);

        // Удаление завершащего слеша
        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            $dir = substr($path, 0, -1);
        }

        return $dir . DIRECTORY_SEPARATOR . $name;
    }
}
