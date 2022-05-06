<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;
use twin\migration\Migration;

abstract class Database extends Component
{
    const TYPE_JSON = 'json';
    const TYPE_MYSQL = 'mysql';
    const TYPE_SQLITE = 'sqlite';

    /**
     * Тип БД.
     * @var string
     */
    protected $type;

    /**
     * Название БД.
     * @var string
     */
    protected $dbname;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        if (!$this->connect()) {
            throw new Exception(500, 'Database connection error: ' . get_called_class());
        }
    }

    /**
     * Тип БД.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Название БД.
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbname;
    }

    /**
     * Создать таблицу для миграций.
     * @param string $table - название таблицы с миграциями
     * @return bool
     */
    abstract public function createMigrationTable(string $table): bool;

    /**
     * Применена ли указанная миграция.
     * @param Migration $migration - экземпляр миграции
     * @return bool
     */
    abstract public function isMigrationApplied(Migration $migration): bool;

    /**
     * Добавить миграцию в БД.
     * @param Migration $migration - экземпляр миграции
     * @return bool
     */
    abstract public function addMigration(Migration $migration): bool;

    /**
     * Удалить миграцию из БД.
     * @param Migration $migration - экземпляр миграции
     * @return bool
     */
    abstract public function deleteMigration(Migration $migration): bool;

    /**
     * Подключиться к БД.
     * @return bool
     */
    abstract protected function connect(): bool;
}
