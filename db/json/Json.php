<?php

namespace twin\db\json;

use twin\db\Database;
use twin\helper\Alias;
use twin\migration\Migration;

class Json extends Database
{
    /**
     * Расширение файлов БД.
     */
    const FILE_EXT = 'json';

    /**
     * Путь до директории с файлами БД.
     * @var string
     */
    public $alias = '@runtime/db/json';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['dbname', 'alias'];

    /**
     * Извлечь данные из таблицы.
     * @param string $table - название таблицы
     * @return array
     */
    public function getData(string $table): array
    {
        $filePath = $this->getFilePath($table);

        if (!is_file($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);

        if ($content === false) {
            return [];
        } else {
            return (array)json_decode($content, true);
        }
    }

    /**
     * Сохранить данные в таблицу.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @return bool
     */
    public function setData(string $table, array $data): bool
    {
        $filePath = $this->getFilePath($table);
        $content = json_encode($data);
        return false !== file_put_contents($filePath, $content, LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    public function createMigrationTable(string $table): bool
    {
        $data = $this->getData($table);
        return $this->setData($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function isMigrationApplied(Migration $migration): bool
    {
        $table = $migration->getManager()->table;
        $items = $this->getData($table);
        $hash = $migration->getHash();
        return array_key_exists($hash, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function addMigration(Migration $migration): bool
    {
        $table = $migration->getManager()->table;
        $items = $this->getData($table);
        $hash = $migration->getHash();

        $items[$hash] = [
            'name' => $migration->getClass(),
            'timestamp' => time(),
        ];

        return $this->setData($table, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMigration(Migration $migration): bool
    {
        $table = $migration->getManager()->table;
        $items = $this->getData($table);
        $hash = $migration->getHash();

        if (array_key_exists($hash, $items)) {
            unset($items[$hash]);
        }

        return $this->setData($table, $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function connect(): bool
    {
        // Создать директорию для хранения файлов БД.
        $databasePath = $this->getDatabasePath();

        if (!is_dir($databasePath)) {
            return mkdir($databasePath, 0775, true);
        }

        return true;
    }

    /**
     * Полный путь до файла с таблицей.
     * @param string $table - название таблицы
     * @return string
     */
    protected function getFilePath(string $table): string
    {
        $fileName = $table . '.' . self::FILE_EXT;
        $alias = "$this->alias/$this->dbname/$fileName";
        return Alias::get($alias);
    }

    /**
     * Путь до директории с БД.
     * @return string
     */
    protected function getDatabasePath(): string
    {
        $alias = "$this->alias/$this->dbname";
        return Alias::get($alias);
    }
}
