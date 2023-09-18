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
    public $path = '@runtime/db/json';

    /**
     * {@inheritdoc}
     */
    protected $type = self::TYPE_JSON;

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['dbname', 'path'];

    /**
     * Кешируемые данные таблиц для предотвращения повторных обращений.
     * @var array
     */
    private static $data = [];

    /**
     * Извлечь данные из таблицы.
     * @param string $table - название таблицы
     * @return array
     */
    public function getData(string $table): array
    {
        if (!isset(static::$data[$this->dbname][$table])) {
            $filePath = $this->getFilePath($table);
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                static::$data[$this->dbname][$table] = json_decode($content, true);
            } else {
                static::$data[$this->dbname][$table] = [];
            }
        }
        return static::$data[$this->dbname][$table];
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
        $result = file_put_contents($filePath, $content, LOCK_EX);
        $result = $result !== false;
        if ($result) {
            static::$data[$this->dbname][$table] = $data;
        }
        return $result;
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

        if (!$this->createMigrationTable($table)) {
            return false;
        }

        $items = $this->getData($table);
        $column = array_column($items, 'hash');

        return in_array($migration->getHash(), $column);
    }

    /**
     * {@inheritdoc}
     */
    public function addMigration(Migration $migration): bool
    {
        $isApplied = $this->isMigrationApplied($migration);

        if ($isApplied) {
            return true;
        }

        $items = $this->getData($migration->getManager()->table);
        $items[] = [
            'hash' => $migration->getHash(),
            'name' => $migration->getClass(),
            'timestamp' => time(),
        ];

        return $this->setData($migration->getManager()->table, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMigration(Migration $migration): bool
    {
        $table = $migration->getManager()->table;
        $items = $this->getData($table);

        foreach ($items as $i => $item) {
            $hash = $item['hash'] ?? null;

            if ($hash == $migration->getHash()) {
                unset($items[$i]);
                break;
            }
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
        if (!file_exists($databasePath)) {
            return mkdir($databasePath, 0775, true);
        }
        return true;
    }

    /**
     * Полный путь до файла с таблицей.
     * @param string $table - название таблицы
     * @return string
     */
    private function getFilePath(string $table): string
    {
        $fileName = $this->getFileName($table);
        $databasePath = $this->getDatabasePath();
        return $databasePath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Путь до директории с БД.
     * @return string
     */
    private function getDatabasePath(): string
    {
        $alias = $this->path . DIRECTORY_SEPARATOR . $this->dbname;
        return Alias::get($alias);
    }

    /**
     * Вернуть название файла таблицы с расширением.
     * @param string $table - название таблицы
     * @return string
     */
    private function getFileName(string $table): string
    {
        return $table . '.' . self::FILE_EXT;
    }
}
