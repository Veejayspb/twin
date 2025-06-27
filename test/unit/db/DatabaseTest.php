<?php

use test\helper\BaseTestCase;
use twin\db\Database;

final class DatabaseTest extends BaseTestCase
{
    const DB = 'db';
    const TABLE = 'table';

    public static array $pk = ['id'];
    public static array $tables = ['user'];
    public static bool $connect = true;

    public function testConstruct()
    {
        self::$connect = false;
        $this->expectExceptionCode(500);
        $this->getDatabase(['dbName' => self::DB]);
    }

    public function testGetPkData()
    {
        $attributes = [
            'id' => 1,
            'name' => 'Trump',
            'date_create' => '2025-06-05',
        ];

        self::$connect = true;
        $database = $this->getDatabase(['dbName' => self::DB]);

        self::$pk = ['id'];
        $expected = ['id' => 1];
        $this->assertSame($expected, $database->getPkData(self::TABLE, $attributes));

        self::$pk = ['id', 'name'];
        $expected = ['id' => 1, 'name' => 'Trump'];
        $this->assertSame($expected, $database->getPkData(self::TABLE, $attributes));

        self::$pk = ['id', 'undefined'];
        $expected = ['id' => 1];
        $this->assertSame($expected, $database->getPkData(self::TABLE, $attributes));

        self::$pk = ['undefined'];
        $expected = [];
        $this->assertSame($expected, $database->getPkData(self::TABLE, $attributes));
    }

    public function testHasTable()
    {
        $database = $this->getDatabase(['dbName' => self::DB]);

        $actual = $database->hasTable('user');
        $this->assertTrue($actual);

        $actual = $database->hasTable('USER');
        $this->assertFalse($actual);

        $actual = $database->hasTable('undefined');
        $this->assertFalse($actual);
    }

    /**
     * @param array $properties
     * @return Database
     */
    protected function getDatabase(array $properties = []): Database
    {
        return new class ($properties) extends Database
        {
            public function findAllByAttributes(string $table, array $conditions): array
            {
                return [];
            }

            public function findByAttributes(string $table, array $conditions): ?array
            {
                return null;
            }

            public function insert(string $table, array $data): ?array
            {
                return null;
            }

            public function update(string $table, array $data, array $conditions): bool
            {
                return false;
            }

            public function delete(string $table, array $conditions): bool
            {
                return false;
            }

            public function getPk(string $table): array
            {
                return DatabaseTest::$pk;
            }

            public function getTables(): array
            {
                return DatabaseTest::$tables;
            }

            protected function connect(): bool
            {
                return DatabaseTest::$connect;
            }

            public function deleteTable(string $name): bool
            {
                return false;
            }
        };
    }
}
