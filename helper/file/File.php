<?php

namespace twin\helper\file;

class File
{
    /**
     * Путь до файла.
     * @var string
     */
    public $path;

    /**
     * @param string $path - путь до файла
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Скопировать файл в новую директорию.
     * @param string $path - путь до директории
     * @return static|bool - FALSE в случае ошибки
     */
    public function copy(string $path)
    {
        if (!$this->exists()) return false;
        if (!is_dir($path) && !mkdir($path, 0775, true)) { // TODO: установить права такие же, как на род. директории
            return false;
        }
        $name = basename($this->path);
        $newPath = $path . DIRECTORY_SEPARATOR . $name;
        if (!copy($this->path, $newPath)) return false;
        return new static($newPath);
    }

    /**
     * Переместить файл в новую директорию.
     * @param string $path - путь до директории
     * @return static|bool - FALSE в случае ошибки
     */
    public function move(string $path)
    {
        $file = $this->copy($path);
        if (!$file) return false;
        $this->delete();
        $this->path = $file->path;
        return $this;
    }

    /**
     * Переименовать файл.
     * @param string $name - новое имя файла
     * @return bool
     */
    public function rename(string $name): bool
    {
        if (!$this->exists()) return false;
        $newPath = dirname($this->path) . DIRECTORY_SEPARATOR . basename($name);
        $result = rename($this->path, $newPath);
        $this->path = $newPath;
        return $result;
    }

    /**
     * Удалить файл.
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists()) return true;
        return unlink($this->path);
    }

    /**
     * Существует ли файл.
     * @return bool
     */
    protected function exists(): bool
    {
        return is_file($this->path);
    }
}
