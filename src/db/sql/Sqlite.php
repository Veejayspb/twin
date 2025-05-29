<?php

namespace twin\db\sql;

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
    protected array $_requiredProperties = ['dbname', 'path'];

    /**
     * {@inheritdoc}
     */
    public function getTables(): bool|array
    {
        $sql = "SELECT `name` FROM `sqlite_master` WHERE type='table' ORDER BY 'name'";
        $items = $this->query($sql);

        if ($items === false) {
            return false;
        }

        return array_column($items, 'name');
    }

    /**
     * {@inheridoc}
     */
    public function getPk(string $table): array
    {
        $items = $this->query("PRAGMA table_info ('$table')");

        if ($items === false) {
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
    public function getAutoIncrement(string $table): bool|string
    {
        $items = $this->query("PRAGMA table_info ('$table')");

        if (empty($items)) {
            return false;
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

        return ($count == 1 && $result !== null) ? $result : false;
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

    /**
     * Полный путь до файла БД.
     * @return string
     */
    private function getFilePath(): string
    {
        $alias = "$this->alias/$this->dbname." . self::FILE_EXT;
        return Alias::get($alias);
    }
}
