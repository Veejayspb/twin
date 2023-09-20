<?php

namespace twin\test\helper;

use twin\migration\Migration;

final class Database extends \twin\db\Database
{
    /**
     * Возвращаемые значения методов.
     * @var array
     */
    public static $_returnValues = [];

    /**
     * {@inheritdoc}
     */
    public function createMigrationTable(string $table): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function isMigrationApplied(Migration $migration): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function addMigration(Migration $migration): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMigration(Migration $migration): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }

    /**
     * {@inheritdoc}
     */
    protected function connect(): bool
    {
        return self::$_returnValues[__FUNCTION__] ?? true;
    }
}
