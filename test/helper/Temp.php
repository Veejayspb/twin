<?php

namespace twin\test\helper;

class Temp
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

        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            return false;
        }

        $result = file_put_contents($path, $content, LOCK_EX);
        return $result !== false;
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
