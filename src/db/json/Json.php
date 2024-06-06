<?php

namespace twin\db\json;

use twin\db\Database;
use twin\helper\Alias;
use twin\migration\Migration;
use twin\model\Model;

class Json extends Database
{
    /**
     * Расширение файлов БД.
     */
    const FILE_EXT = 'json';

    /**
     * Название виртуального поля, в которое помещается значение первичного ключа.
     */
    const PK_FIELD = '_pk_';

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
     * Добавить запись.
     * @param string $table - название таблицы
     * @param array $row - данные
     * @return string|bool
     */
    public function insert(string $table, array $row)
    {
        $key = $this->generateKey($table);

        if ($key === null) {
            return false;
        }

        $data = $this->getData($table);
        $data[$key] = $row;

        if ($this->setData($table, $data)) {
            return $key;
        } else {
            return false;
        }
    }

    /**
     * Обновить запись.
     * @param string $table - название таблицы
     * @param array $row - данные
     * @param string $key - ключ
     * @return bool
     */
    public function update(string $table, array $row, string $key): bool
    {
        $data = $this->getData($table);

        if (!array_key_exists($key, $data)) {
            return false;
        }

        $data[$key] = $row;
        return $this->setData($table, $data);
    }

    /**
     * Удалить запись.
     * @param string $table - название таблицы
     * @param string $key - ключ
     * @return bool
     */
    public function delete(string $table, string $key): bool
    {
        $data = $this->getData($table);

        if (!array_key_exists($key, $data)) {
            return true;
        }

        unset($data[$key]);
        return $this->setData($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function createModel(Model $model): bool
    {
        $table = $model::tableName();
        $attributes = $model->getAttributes();

        if (array_key_exists(static::PK_FIELD, $attributes)) {
            unset($attributes[static::PK_FIELD]);
        }

        $key = $this->insert($table, $attributes);

        if (!$key) {
            return false;
        }

        $model->setAttribute(static::PK_FIELD, $key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateModel(Model $model): bool
    {
        if (!$model->hasAttribute(static::PK_FIELD)) {
            return false;
        }

        $table = $model::tableName();
        $attributes = $model->getAttributes();
        $key = $model->getAttribute(static::PK_FIELD);
        unset($attributes[static::PK_FIELD]);

        return $this->update($table, $attributes, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteModel(Model $model): bool
    {
        if (!$model->hasAttribute(static::PK_FIELD)) {
            return false;
        }

        $table = $model::tableName();
        $key = $model->getAttribute(static::PK_FIELD);

        return $this->delete($table, $key);
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
    public function getPk(string $table): array
    {
        return [static::PK_FIELD];
    }

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
     * Сгенерировать уникальный хэш в рамках таблицы.
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
