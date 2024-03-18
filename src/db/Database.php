<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;
use twin\migration\Migration;

abstract class Database extends Component
{
    /**
     * Название БД.
     * @var string
     */
    public $dbname;

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
