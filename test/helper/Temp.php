<?php

namespace test\helper;

use twin\helper\file\Dir;
use twin\helper\file\File;

final class Temp
{
    /**
     * Создать файл.
     * @param string $relPath - относительный путь до файла
     * @param string $content - содержимое файла
     * @return bool
     */
    public function createFile(string $relPath, string $content): bool
    {
        $path = $this->getFilePath($relPath);

        if (is_dir($path)) {
            return false;
        }

        $result = file_put_contents($path, $content, LOCK_EX);
        return $result !== false;
    }

    /**
     * Создать директорию.
     * @param string $relPath
     * @return bool
     */
    public function createDir(string $relPath): bool
    {
        $path = $this->getFilePath($relPath);
        return mkdir($path);
    }

    /**
     * Удалить файл/директорию.
     * @param string $relPath
     * @return bool
     */
    public function remove(string $relPath): bool
    {
        $path = $this->getFilePath($relPath);

        if (!file_exists($path)) {
            return true;
        }

        $file = is_file($path) ? new File($path) : new Dir($path);
        return $file->delete();
    }

    /**
     * Очистить директорию с временными файлами.
     * @return bool
     */
    public function clear(): bool
    {
        $path = $this->getTempPath();
        $dir = new Dir($path);
        $children = $dir->getChildren();
        $result = true;

        foreach ($children as $child) {
            $name = $child->getName();

            if (in_array($name, ['.gitignore'])) {
                continue;
            }

            $result = $child->delete() ? $result : false;
        }

        return $result;
    }

    /**
     * Путь до указанного файла.
     * @param string $relPath - относительный путь до файла: path/to/file.ext
     * @return string
     */
    public function getFilePath(string $relPath): string
    {
        $tempPath = $this->getTempPath();
        return $tempPath . DIRECTORY_SEPARATOR . $relPath;
    }

    /**
     * Путь до директории с временными файлами.
     * @return string
     */
    protected function getTempPath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'temp';
    }
}
