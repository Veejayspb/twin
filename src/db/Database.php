<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;
use twin\criteria\Criteria;
use twin\migration\Migration;

abstract class Database extends Component
{
    /**
     * Название БД.
     * @var string
     */
    public string $dbname;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (!$this->connect()) {
            throw new Exception(500, 'Database connection error: ' . get_called_class());
        }
    }

    /**
     * Поиск записи, используя критерии.
     * @param Criteria $criteria
     * @return array|null
     */
    public function find(Criteria $criteria): ?array
    {
        $criteria->limit = 1;
        $rows = $criteria->query($this);
        return $rows ? current($rows) : null;
    }

    /**
     * Поиск записей, используя критерии.
     * @param Criteria $criteria
     * @return array
     */
    public function findAll(Criteria $criteria): array
    {
        return $criteria->query($this);
    }

    /**
     * Поиск всех записей по значению атрибутов.
     * @param string $table
     * @param array $attributes
     * @return array
     */
    abstract public function findAllByAttributes(string $table, array $attributes): array;

    /**
     * Поиск записи по значению атрибутов.
     * @param string $table
     * @param array $attributes
     * @return array|null
     */
    abstract public function findByAttributes(string $table, array $attributes): ?array;

    /**
     * Вернуть названия столбцов, входящих в первичный ключ.
     * @param string $table - название таблицы
     * @return array
     */
    abstract public function getPk(string $table): array;

    /**
     * Создать таблицу для миграций.
     * @param string $table - название таблицы с миграциями
     * @return bool
     */
    abstract public function createMigrationTable(string $table): bool;

    /**
     * Применена ли указанная миграция.
     * @param Migration $migration
     * @return bool
     */
    abstract public function isMigrationApplied(Migration $migration): bool;

    /**
     * Добавить миграцию в БД.
     * @param Migration $migration
     * @return bool
     */
    abstract public function addMigration(Migration $migration): bool;

    /**
     * Удалить миграцию из БД.
     * @param Migration $migration
     * @return bool
     */
    abstract public function deleteMigration(Migration $migration): bool;

    /**
     * Подключиться к БД.
     * @return bool
     */
    abstract protected function connect(): bool;
}
