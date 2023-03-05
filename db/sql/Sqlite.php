<?php

namespace twin\db\sql;

use twin\common\Exception;
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
    protected $path = '@runtime/db/sqlite';

    /**
     * {@inheritdoc}
     */
    protected $type = self::TYPE_SQLITE;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        if (!isset($properties['dbname'], $properties['path'])) {
            throw new Exception(500, self::class . ' - required properties not specified: dbname, path');
        }

        parent::__construct($properties);
    }

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
            if (empty($item['pk'])) continue;
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
            if (empty($item['pk'])) continue;
            if (mb_strtoupper($item['type']) == 'INTEGER') $count++;
            $result = $item['name'];
        }

        return $count == 1 && $result !== null ? $result : false;
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
        $path = Alias::get($this->path);
        
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
        $fileName = $this->getFileName();
        return Alias::get($this->path) . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Вернуть название файла БД с расширением.
     * @return string
     */
    private function getFileName(): string
    {
        return $this->dbname . '.' . self::FILE_EXT;
    }
}
