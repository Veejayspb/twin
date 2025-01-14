<?php

use twin\db\sql\Sql;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;

final class SqlTest extends BaseTestCase
{
    /**
     * Значение, возвращаемое одноименный методом библиотеки PDO.
     */
    const LAST_INSERT_ID = 1;

    /**
     * Последний SQL-запрос к БД.
     * @var string
     */
    public static $lastSql;

    /**
     * Последний набор параметров к SQL-запросу.
     * @var array
     */
    public static $lastParams;

    public function testQuery()
    {
        $items = [
            [
                'prepare' => true,
                'execute' => true,
                'expected' => [],
            ],
            [
                'prepare' => true,
                'execute' => false,
                'expected' => false,
            ],
            [
                'prepare' => false,
                'execute' => true,
                'expected' => false,
            ],
            [
                'prepare' => false,
                'execute' => false,
                'expected' => false,
            ],
        ];

        $db = $this->mock(Sql::class, null, null);
        $proxy = new ObjectProxy($db);

        foreach ($items as $item) {
            $proxy->connection = $this->getPDO($item['prepare'], $item['execute']);
            $actual = $db->query("some sql statement");

            $this->assertSame($item['expected'], $actual);
        }
    }

    public function testExecute()
    {
        $items = [
            [
                'prepare' => true,
                'execute' => true,
                'expected' => true,
            ],
            [
                'prepare' => true,
                'execute' => false,
                'expected' => false,
            ],
            [
                'prepare' => false,
                'execute' => true,
                'expected' => false,
            ],
            [
                'prepare' => false,
                'execute' => false,
                'expected' => false,
            ],
        ];

        $db = $this->mock(Sql::class, null, null);
        $proxy = new ObjectProxy($db);

        foreach ($items as $item) {
            $proxy->connection = $this->getPDO($item['prepare'], $item['execute']);
            $actual = $db->execute("some sql statement");

            $this->assertSame($item['expected'], $actual);
        }
    }

    public function testInsert()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->insert('tbl', ['name' => 'some name']);
        $this->assertFalse($result);

        // Передан пустой массив данных
        $db = $this->getSql(true);
        $result = $db->insert('tbl', []);
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->insert('tbl', ['name' => 'some name']);
        $this->assertSame(self::LAST_INSERT_ID, $result);
        $this->assertSame('INSERT INTO `tbl` (`name`) VALUES (:name)', self::$lastSql);
        $this->assertSame([':name' => 'some name'], self::$lastParams);
    }

    public function testUpdate()
    {
        $prefix = Sql::PREFIX;

        // Передан пустой массив данных
        $db = $this->getSql(true);
        $result = $db->update('tbl', [], 'name=:name', [':name' => 'old']);
        $this->assertTrue($result);
        $this->assertNull(self::$lastSql);
        $this->assertNull(self::$lastParams);

        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->update('tbl', ['name' => 'new'], 'name=:name', [':name' => 'old']);
        $this->assertFalse($result);

        // Пустое условие WHERE
        $db = $this->getSql(true);
        $result = $db->update('tbl', ['name' => 'new']);
        $this->assertTrue($result);
        $this->assertSame("UPDATE `tbl` SET `name`=:{$prefix}name", self::$lastSql);
        $this->assertSame([":{$prefix}name" => 'new'], self::$lastParams);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->update('tbl', ['name' => 'new'], 'name=:name', [':name' => 'old']);
        $this->assertTrue($result);
        $this->assertSame("UPDATE `tbl` SET `name`=:{$prefix}name WHERE name=:name", self::$lastSql);
        $this->assertSame([':name' => 'old', ":{$prefix}name" => 'new'], self::$lastParams);
    }

    public function testDelete()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->delete('tbl', 'name=:name', [':name' => 'old']);
        $this->assertFalse($result);

        // Пустое условие WHERE
        $db = $this->getSql(true);
        $result = $db->delete('tbl');
        $this->assertTrue($result);
        $this->assertSame('DELETE FROM `tbl`', self::$lastSql);
        $this->assertSame([], self::$lastParams);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->delete('tbl', 'name=:name', [':name' => 'old']);
        $this->assertTrue($result);
        $this->assertSame('DELETE FROM `tbl` WHERE name=:name', self::$lastSql);
        $this->assertSame([':name' => 'old'], self::$lastParams);
    }

    public function testCreateTable()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->createTable('tbl', ['id' => 'INT NOT NULL'], ['PRIMARY KEY (`id`)']);
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->createTable('tbl', ['id' => 'INT NOT NULL'], ['PRIMARY KEY (`id`)']);
        $this->assertTrue($result);
        $this->assertSame('CREATE TABLE IF NOT EXISTS `tbl` (`id` INT NOT NULL, PRIMARY KEY (`id`))', self::$lastSql);
        $this->assertSame([], self::$lastParams);
    }

    public function testDeleteTable()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->deleteTable('tbl');
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->deleteTable('tbl');
        $this->assertTrue($result);
        $this->assertSame('DROP TABLE IF EXISTS `tbl`', self::$lastSql);
        $this->assertSame([], self::$lastParams);
    }

    public function testHasTable()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->hasTable('tbl');
        $this->assertFalse($result);

        // Успешный запрос - нет такой таблицы
        $db = $this->getSql(true);
        $result = $db->hasTable('notexists');
        $this->assertFalse($result);

        // Успешный запрос - такая таблица имеется
        $db = $this->getSql(true);
        $result = $db->hasTable('tbl');
        $this->assertTrue($result);
    }

    public function testTransactionCommit()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->transactionCommit();
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->transactionCommit();
        $this->assertTrue($result);
        $this->assertSame('COMMIT', self::$lastSql);
    }

    public function testTransactionRollback()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->transactionRollback();
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->transactionRollback();
        $this->assertTrue($result);
        $this->assertSame('ROLLBACK', self::$lastSql);
    }

    public function testCreateMigrationTable()
    {
        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->createMigrationTable('migration');
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->createMigrationTable('migration');
        $this->assertTrue($result);
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `migration` (`hash` VARCHAR(32) NOT NULL, `name` TEXT NOT NULL, `timestamp` INT NOT NULL, PRIMARY KEY (`hash`))',
            self::$lastSql
        );
    }

    public function testIsMigrationApplied()
    {
        $migration = $this->getMigration('m_231013_060606_name');

        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->isMigrationApplied($migration);
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->isMigrationApplied($migration);
        $this->assertTrue($result);
    }

    public function testAddMigration()
    {
        $migration = $this->getMigration('m_231013_060606_name');

        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->addMigration($migration);
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->addMigration($migration);
        $this->assertTrue($result);
    }

    public function testDeleteMigration()
    {
        $migration = $this->getMigration('m_231013_060606_name');

        // Эмуляция ошибки в PDO
        $db = $this->getSql(false);
        $result = $db->deleteMigration($migration);
        $this->assertFalse($result);

        // Успешный запрос
        $db = $this->getSql(true);
        $result = $db->deleteMigration($migration);
        $this->assertTrue($result);
    }

    public function testFindAllByAttributes()
    {
        $db = $this->getDatabase();
        $db->findAllByAttributes('test', ['name' => 'vasya']);
        $sql = "SELECT * FROM `test` WHERE `name`=:name";
        $params = [':name' => 'vasya'];

        $this->assertSame($sql, $db->lastSql);
        $this->assertSame($params, $db->lastParams);
    }

    public function testFindByAttributes()
    {
        $db = $this->getDatabase();
        $db->findAllByAttributes('test', ['name' => 'vasya', 'type_id' => 1]);
        $sql = "SELECT * FROM `test` WHERE `name`=:name AND `type_id`=:type_id";
        $params = [':name' => 'vasya', ':type_id' => 1];

        $this->assertSame($sql, $db->lastSql);
        $this->assertSame($params, $db->lastParams);
    }

    /**
     * @param bool $execute
     * @return Sql
     */
    protected function getSql(bool $execute = true): Sql
    {
        $mock = $this->mock(Sql::class, null, null, [
            'query' => function ($sql, $params) use ($execute) {
                self::$lastSql = $sql;
                self::$lastParams = $params;
                return $execute ? [['id' => 1, 'name' => 'name']] : false;
            },
            'execute' => function ($sql, $params) use ($execute) {
                self::$lastSql = $sql;
                self::$lastParams = $params;
                return $execute;
            },
            'getTables' => $execute ? ['tbl'] : false,
            'getAutoIncrement' => function () {
                return 'id';
            },
        ]);

        $proxy = new ObjectProxy($mock);
        $proxy->connection = $this->getPDO(true, true);

        return $mock;
    }

    /**
     * @param bool $prepare
     * @param bool $execute
     * @return PDO
     */
    protected function getPDO(bool $prepare, bool $execute): PDO
    {
        $statement = $this->mock(PDOStatement::class, null, [], [
            'execute' => $execute,
            'fetchAll' => [],
        ]);

        return $this->mock(PDO::class, null, null, [
            'prepare' => $prepare ? $statement : false,
            'lastInsertId' => self::LAST_INSERT_ID,
        ]);
    }

    /**
     * @param string $class
     * @return Migration
     */
    protected function getMigration(string $class): Migration
    {
        $manager = new MigrationManager(['alias' => '@test/temp']);
        return $this->mock(Migration::class, $class, [$manager]);
    }

    /**
     * @return Sql
     */
    protected function getDatabase(): Sql
    {
        return new class extends Sql
        {
            /**
             * @var string
             */
            public $lastSql;

            /**
             * @var array
             */
            public $lastParams = [];

            public function getPk(string $table): array
            {
                return [];
            }

            protected function connect(): bool
            {
                return true;
            }

            public function getAutoIncrement(string $table)
            {
                return false;
            }

            public function transactionBegin(): bool
            {
                return true;
            }

            public function getTables()
            {
                return [];
            }

            public function query(string $sql, array $params = [])
            {
                $this->lastSql = $sql;
                $this->lastParams = $params;
                return [];
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        self::$lastSql = null;
        self::$lastParams = null;
    }
}
