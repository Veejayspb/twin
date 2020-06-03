<?php

namespace twin\helper;

use DirectoryIterator;

class File
{
    /**
     * Рекурсивное копирование директории.
     * @param string $path - путь до исходной директории
     * @param string $dest - путь до конечной директории
     * @return bool
     */
    public static function copy(string $path, string $dest): bool
    {
        if (!is_dir($path)) return false;
        if (!is_dir($dest) && !mkdir($dest, 0775, true)) {
            return false;
        }

        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                if (!copy($item->getRealPath(), $dest . '/' . $item->getFilename())) return false;
            } elseif (!$item->isDot() && $item->isDir()) {
                if (!static::copy($item->getRealPath(), $dest . '/' . $item)) return false;
            }
        }
        return true;
    }

    /**
     * Рекурсивное удаление директории и всех файлов.
     * @param string $path - путь до директории
     * @return bool
     */
    public static function remove(string $path): bool
    {
        return false; // TODO...
    }
}
