<?php

namespace twin\cache;

use twin\helper\Alias;

class CacheFile extends Cache
{
    /**
     * Расширение файлов.
     */
    const FILE_EXT = 'json';

    /**
     * Путь до директории с файлами кеша.
     * @var string
     */
    public $path = '@runtime/cache';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['path'];

    /**
     * {@inheritdoc}
     */
    protected function saveItem(CacheItem $item): bool
    {
        $path = $this->getFilePath($item);
        return (bool)file_put_contents($path, $item, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractItem(string $key)
    {
        $item = new CacheItem;
        $item->key = $key;

        $path = $this->getFilePath($item);
        if (!file_exists($path)) return false;
        $content = file_get_contents($path);
        if ($content === false) return false;
        $data = json_decode($content, true);
        return $item->setProperties($data);
    }

    /**
     * Путь до файла кеша.
     * @param CacheItem $item - объект с данными кеша
     * @return string
     */
    private function getFilePath(CacheItem $item): string
    {
        $alias = $this->path . '/' . $item->getHash() . '.' . static::FILE_EXT;
        $dir = Alias::get($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return Alias::get($alias);
    }
}
