<?php

use test\helper\BaseTestCase;
use twin\db\Sql;

final class SqlTest extends BaseTestCase
{
    const TABLE = 'table';

    public function testCreateTable()
    {
        $sql = $this->getSql();
        $table = self::TABLE;

        $sql->createTable(
            $table,
            ['id' => 'INT NOT NULL', 'name' => 'VARCHAR(255) NULL'],
            ['PRIMARY KEY (`id`)']
        );
        $expected = "CREATE TABLE IF NOT EXISTS `{$table}` (`id` INT NOT NULL, `name` VARCHAR(255) NULL, PRIMARY KEY (`id`))";
        $this->assertSame($expected, $sql->sql);
    }

    public function testDeleteTable()
    {
        $sql = $this->getSql();
        $table = self::TABLE;

        $sql->deleteTable($table);
        $expected = "DROP TABLE IF EXISTS `$table`";
        $this->assertSame($expected, $sql->sql);
    }

    public function testTransactionCommit()
    {
        $sql = $this->getSql();

        $sql->transactionCommit();
        $expected = "COMMIT";
        $this->assertSame($expected, $sql->sql);
    }

    public function testTransactionRollback()
    {
        $sql = $this->getSql();

        $sql->transactionRollback();
        $expected = "ROLLBACK";
        $this->assertSame($expected, $sql->sql);
    }

    public function testFindAllByAttributes()
    {
        $sql = $this->getSql();
        $table = self::TABLE;
        $sql->findAllByAttributes($table, ['id' => 1, 'name' => 'test']);

        $expected = "SELECT * FROM `$table` WHERE `id`=:id AND `name`=:name";
        $this->assertSame($expected, $sql->sql);

        $expected = [':id' => 1, ':name' => 'test'];
        $this->assertSame($expected, $sql->params);
    }

    public function testFindByAttributes()
    {
        $sql = $this->getSql();
        $table = self::TABLE;
        $sql->findByAttributes($table, ['id' => 1, 'name' => 'test']);

        $expected = "SELECT * FROM `$table` WHERE `id`=:id AND `name`=:name LIMIT 1";
        $this->assertSame($expected, $sql->sql);

        $expected = [':id' => 1, ':name' => 'test'];
        $this->assertSame($expected, $sql->params);
    }

    public function testInsert()
    {
        $sql = $this->getSql();
        $table = self::TABLE;
        $sql->insert($table, ['name' => 'test', 'age' => 37]);

        $expected = "INSERT INTO `$table` (`name`, `age`) VALUES (:name, :age)";
        $this->assertSame($expected, $sql->sql);

        $expected = [':name' => 'test', ':age' => 37];
        $this->assertSame($expected, $sql->params);
    }

    public function testUpdate()
    {
        $sql = $this->getSql();
        $table = self::TABLE;
        $prefix = Sql::PREFIX;
        $sql->update($table, ['name' => 'test', 'age' => 37], ['id' => 1]);

        $expected = "UPDATE `$table` SET `name`=:{$prefix}name, `age`=:{$prefix}age WHERE `id`=:id";
        $this->assertSame($expected, $sql->sql);

        $expected = [":{$prefix}name" => 'test', ":{$prefix}age" => 37, ':id' => 1];
        $this->assertSame($expected, $sql->params);
    }

    public function testDelete()
    {
        $sql = $this->getSql();
        $table = self::TABLE;
        $sql->delete($table, ['id' => 1, 'age' => 37]);

        $expected = "DELETE FROM `$table` WHERE `id`=:id AND `age`=:age";
        $this->assertSame($expected, $sql->sql);

        $expected = [':id' => 1, ':age' => 37];
        $this->assertSame($expected, $sql->params);
    }

    /**
     * @return Sql
     */
    protected function getSql()
    {
        return new class extends Sql
        {
            public string $dbName = 'db';
            public string $sql = '';
            public array $params = [];

            public function getPk(string $table): array
            {
                return [];
            }

            public function getTables(): array
            {
                return [];
            }

            protected function connect(): bool
            {
                return true;
            }

            public function getAutoIncrement(string $table): ?string
            {
                return null;
            }

            public function transactionBegin(): bool
            {
                return true;
            }

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

            protected function lastInsertId(): string
            {
                return '1';
            }
        };
    }
}
