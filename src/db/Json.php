<?php

namespace twin\db;

use twin\helper\Alias;
use twin\helper\file\Dir;

class Json extends Database
{
    /**
     * Расширение файлов БД.
     */
    const FILE_EXT = 'json';

    /**
     * Паттерн названия таблицы.
     */
    const PATTERN_TABLE = '/^[a-z0-9_]+$/';

    /**
     * Путь до директории с файлами БД.
     * @var string
     */
    public string $alias = '@runtime/db/json';

    /**
     * Название виртуального поля, в которое дублируется значение первичного ключа.
     * @var string
     */
    public string $pkField = '_pk_';

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['dbName', 'pkField', 'alias'];

    /**
     * {@inheritdoc}
     */
    public function getPk(string $table): array
    {
        return [$this->pkField];
    }

    /**
     * {@inheritdoc}
     */
    public function getTables(): array
    {
        $path = $this->getDatabasePath();
        $dir = new Dir($path);
        $children = $dir->getChildren();
        $result = [];

        foreach ($children as $child) {
            if (!$child->isFile()) {
                continue;
            }

            $ext = $child->getExtFromName();

            if ($ext != static::FILE_EXT) {
                continue;
            }

            $result[] = $child->getNameWithoutExt();
        }

        return $result;
    }


    /**
     * Создать таблицу.
     * @param string $name
     * @return bool
     */
    public function createTable(string $name): bool
    {
        if ($this->hasTable($name)) {
            return true;
        }

        if (!$this->isValidName($name)) {
            return false;
        }

        $path = $this->getFilePath($name);
        return false !== file_put_contents($path, json_encode([]), LOCK_EX);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTable(string $name): bool
    {
        if (!$this->hasTable($name)) {
            return false;
        }

        $path = $this->getFilePath($name);
        return unlink($path);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByAttributes(string $table, array $conditions): array
    {
        $dbData = $this->getData($table);
        $result = [];

        foreach ($dbData as $key => $row) {
            $intersect = array_intersect_assoc($conditions, $row);

            if ($intersect == $conditions) {
                $result[$key] = $row;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findByAttributes(string $table, array $conditions): ?array
    {
        $dbData = $this->getData($table);

        foreach ($dbData as $row) {
            $intersect = array_intersect_assoc($conditions, $row);

            if ($intersect == $conditions) {
                return $row;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table, array $data): ?array
    {
        $key = $this->generateKey($table);

        if ($key === null) {
            return null;
        }

        $dbData = $this->getData($table);
        $dbData[$key] = $data;

        if ($this->setData($table, $dbData)) {
            return [$this->pkField => $key];
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table, array $data, array $conditions): bool
    {
        $dbData = $this->getData($table);

        foreach ($dbData as $key => $row) {
            $intersect = array_intersect_assoc($conditions, $row);

            if ($intersect == $conditions) {
                $dbData[$key] = $data + $row;
            }
        }

        return $this->setData($table, $dbData);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $table, array $conditions): bool
    {
        $dbData = $this->getData($table);

        foreach ($dbData as $key => $row) {
            $intersect = array_intersect_assoc($conditions, $row);

            if ($intersect == $conditions) {
                unset($dbData[$key]);
            }
        }

        return $this->setData($table, $dbData);
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
     * Извлечь данные из таблицы.
     * @param string $table - название таблицы
     * @return array
     */
    public function getData(string $table): array
    {
        if (!$this->hasTable($table)) {
            return [];
        }

        $filePath = $this->getFilePath($table);
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
        if (!$this->hasTable($table)) {
            return false;
        }

        $filePath = $this->getFilePath($table);
        $content = json_encode($data);
        return false !== file_put_contents($filePath, $content, LOCK_EX);
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
        $alias = "$this->alias/$this->dbName/$fileName";
        return Alias::get($alias);
    }

    /**
     * Путь до директории с БД.
     * @return string
     */
    protected function getDatabasePath(): string
    {
        $alias = "$this->alias/$this->dbName";
        return Alias::get($alias);
    }

    /**
     * Сгенерировать уникальный ключ в рамках таблицы.
     * @param string $table - название таблицы
     * @return string|null
     */
    protected function generateKey(string $table): ?string
    {
        $data = $this->getData($table);

        for ($i = 0; $i < 100; $i++) {
            $microtime = microtime(true);
            $hash = md5($microtime);

            if (!array_key_exists($hash, $data)) {
                return $hash;
            }

            usleep(1);
        }

        return null;
    }

    /**
     * Проверка названия таблицы на валидность.
     * @param string $table
     * @return bool
     */
    protected function isValidName(string $table): bool
    {
        return preg_match(self::PATTERN_TABLE, $table);
    }
}
