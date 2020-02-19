<?php

namespace twin\db\json;

use twin\common\Exception;
use twin\db\Database;
use twin\Twin;

class Json extends Database
{
    /**
     * Расширение файлов БД.
     */
    const FILE_EXT = 'json';

    /**
     * {@inheritdoc}
     */
    protected $type = self::TYPE_JSON;

    /**
     * Путь до директории с файлами БД.
     * @var string
     */
    protected $path;

    /**
     * Кешируемые данные таблиц для предотвращения повторных обращений.
     * @var array
     */
    private static $data = [];

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
    protected function connect(): bool
    {
        // Создать директорию для хранения файлов БД.
        $databasePath = $this->getDatabasePath();
        if (!file_exists($databasePath)) {
            return mkdir($databasePath, 0777, true);
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
        return Twin::getAlias($alias);
    }

    /**
     * Вернуть название файла таблицы с расширением.
     * @param string $table - название таблицы
     * @return string
     */
    private function getFileName(string $table): string
    {
        return "$table." . self::FILE_EXT;
    }
}
