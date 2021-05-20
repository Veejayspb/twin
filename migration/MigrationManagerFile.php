<?php

namespace twin\migration;

use twin\Twin;

class MigrationManagerFile extends MigrationManager
{
    /**
     * Файл для хранения данных по последней миграции.
     */
    const RUNTIME_STORAGE = '@runtime/migration.json';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->createDir();
    }

    /**
     * {@inheritdoc}
     */
    protected function setTimestamp(int $timestamp): bool
    {
        $path = Twin::getAlias(static::RUNTIME_STORAGE);

        $content = json_encode([
            'timestamp' => $timestamp,
        ]);

        return (bool)file_put_contents($path, $content, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTimestamp(): int
    {
        $path = Twin::getAlias(static::RUNTIME_STORAGE);
        if (!is_file($path)) return 0;

        $content = file_get_contents($path);
        if ($content === false) return 0;

        $data = json_decode($content, true);
        return (int)($data['timestamp'] ?? 0);
    }

    /**
     * Создать директорию, если не сущ-ет.
     * @return bool
     */
    private function createDir(): bool
    {
        $path = Twin::getAlias($this->path);

        if (!is_dir($path)) {
            return mkdir($path, 0775, true);
        }

        return true;
    }
}
