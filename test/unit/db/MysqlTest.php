<?php

use test\helper\BaseTestCase;
use twin\db\Mysql;

final class MysqlTest extends BaseTestCase
{
    const TABLE = 'table';

    public function testGetTables()
    {
        $sqlite = $this->getMysql();
        $sqlite->getTables();

        $expected = 'SHOW TABLES';
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testGetPk()
    {
        $sqlite = $this->getMysql();
        $table = self::TABLE;
        $sqlite->getPk($table);

        $expected = "SHOW KEYS FROM `$table` WHERE Key_name='PRIMARY'";
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testGetAutoIncrement()
    {
        $sqlite = $this->getMysql();
        $table = self::TABLE;
        $sqlite->getAutoIncrement($table);

        $expected = "SHOW FULL COLUMNS FROM `$table`";
        $this->assertSame($expected, $sqlite->sql);
    }

    public function testTransactionBegin()
    {
        $sqlite = $this->getMysql();
        $sqlite->transactionBegin();

        $expected = 'START TRANSACTION';
        $this->assertSame($expected, $sqlite->sql);
    }

    /**
     * @return Mysql
     */
    protected function getMysql()
    {
        return new class extends Mysql
        {
            public string $dbName = 'db';
            public string $username = 'user';
            public string $password = 'pass';
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

            public function connect(): bool
            {
                return true;
            }
        };
    }
}
