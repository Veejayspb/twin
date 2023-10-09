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
    public $alias = '@runtime/db/sqlite';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['dbname', 'path'];

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        $sql = "SELECT `name` FROM `sqlite_master` WHERE type='table' ORDER BY 'name'";
        $result = $this->query($sql, [], true);
        return array_column($result, 'name');
    }

    /**
     * {@inheridoc}
     */
    public function getPk(string $table): array
    {
        $items = $this->query("PRAGMA table_info ('$table')", [], true);
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
    public function getAutoIncrement(string $table)
    {
        $items = $this->query("PRAGMA table_info ('$table')", [], true);
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
