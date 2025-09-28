<?php

namespace twin\db;

use twin\helper\Alias;
use PDO;

class Sqlite extends Sql
{
    /**
     * Расширение файлов БД.
     */
    const FILE_EXT = 'db';

    /**
     * Путь до директории с файлами БД.
     * @var string
     */
    public string $alias = '@runtime/db/sqlite';

    /**
     * {@inheritdoc}
     */
    public function getTables(): array
    {
        $sql = "SELECT `name` FROM `sqlite_master` WHERE `type`='table' ORDER BY 'name'";
        $items = $this->query($sql);

        if ($items === null) {
            return [];
        }

        return array_column($items, 'name');
    }

    /**
     * {@inheridoc}
     */
    public function getPk(string $table): array
    {
        $items = $this->query("PRAGMA table_info ('$table')");

        if ($items === null) {
            return [];
        }

        $result = [];

        foreach ($items as $item) {
            if (empty($item['pk'])) {
                continue;
            }

            $result[] = $item['name'];
        }

        return $result;
    }

    /**
     * {@inheridoc}
     */
    public function getAutoIncrement(string $table): ?string
    {
        $items = $this->query("PRAGMA table_info ('$table')");

        if (empty($items)) {
            return null;
        }

        $result = null;
        $count = 0;

        foreach ($items as $item) {
            if (empty($item['pk'])) {
                continue;
            }

            if (mb_strtoupper($item['type']) == 'INTEGER') {
                $count++;
            }

            $result = $item['name'];
        }

        return ($count == 1 && $result !== null) ? $result : null;
    }

    /**
     * {@inheritdoc}
     */
    public function transactionBegin(): bool
    {
        return $this->execute('BEGIN TRANSACTION');
    }

    /**
     * {@inheritdoc}
     */
    protected function connect(): bool
    {
        if (!$this->createDir()) {
            return false;
        }

        $path = $this->getFilePath();
        $this->connection = new PDO("sqlite:$path");
        return true;
    }

    /**
     * Полный путь до файла БД.
     * @return string
     */
    protected function getFilePath(): string
    {
        $alias = "$this->alias/$this->dbName." . self::FILE_EXT;
        return Alias::get($alias);
    }

    /**
     * Создать директорию для хранения файлов БД.
     * @return bool
     */
    private function createDir(): bool
    {
        $path = Alias::get($this->alias);

        if (!file_exists($path)) {
            return mkdir($path, 0775, true);
        }

        return true;
    }
}
