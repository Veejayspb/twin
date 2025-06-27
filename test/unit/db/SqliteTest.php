<?php

use test\helper\BaseTestCase;
use twin\db\Sqlite;

final class SqliteTest extends BaseTestCase
{
    const TABLE = 'table';

    public function testGetTables()
    {
        $sqlite = $this->getSqlite();
        $sqlite->getTables();

        $expected = "SELECT `name` FROM `sqlite_master` WHERE `type`='table' ORDER BY 'name'";
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testGetPk()
    {
        $sqlite = $this->getSqlite();
        $table = self::TABLE;
        $sqlite->getPk($table);

        $expected = "PRAGMA table_info ('$table')";
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testGetAutoIncrement()
    {
        $sqlite = $this->getSqlite();
        $table = self::TABLE;
        $sqlite->getAutoIncrement($table);

        $expected = "PRAGMA table_info ('$table')";
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testTransactionBegin()
    {
        $sqlite = $this->getSqlite();
        $sqlite->transactionBegin();

        $expected = 'BEGIN TRANSACTION';
        $this->assertSame($expected, $sqlite->sql);
    }

    /**
     * @return Sqlite
     */
    protected function getSqlite()
    {
        return new class extends Sqlite
        {
            public string $dbName = 'db';
            public string $sql = '';
            public array $params = [];

            public function query(string $sql, array $params = []): ?array
            {
                $this->sql = $sql;
                $this->params = $params;
                return null;
            }

            public function execute(string $sql, array $params = []): bool
            {
                $this->sql = $sql;
                $this->params = $params;
                return true;
            }
        };
    }
}
