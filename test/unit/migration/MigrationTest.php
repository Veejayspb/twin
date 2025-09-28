<?php

use twin\db\Database;
use twin\helper\Alias;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use test\helper\Temp;

final class MigrationTest extends BaseTestCase
{
    /**
     * Корректное название класса миграции.
     */
    const CLASS_NAME = 'm_231007_040500_name';

    public function testConstruct()
    {
        $items = [
            'm_230918_165900_test' => 0,
            'm_230918_165900_0000' => 0,
            'm_000000_000000_test_name' => 0,
            'qqq' => 500,
            't_000000_000000_test' => 500,
            'm_000000_000000_Test' => 500,
            'm_2309_165900_test' => 500,
            'm_230918_1659_test' => 500,
            'm_230918_165900_' => 500,
            'm_230918_165900' => 500,
        ];

        foreach ($items as $className => $expectedCode) {
            $code = $this->catchExceptionCode(function () use ($className) {
                $this->getMigration($className);
            });

            $this->assertSame($expectedCode, $code);
        }
    }

    public function testGetManager()
    {
        $migration = $this->getMigration(self::CLASS_NAME);
        $proxy = new ObjectProxy($migration);

        $this->assertSame($proxy->manager, $migration->getManager());
    }

    public function testClass()
    {
        $migration = $this->getMigration(self::CLASS_NAME);
        $this->assertSame(self::CLASS_NAME, $migration->getClass());
    }

    public function testGetName()
    {
        $items = [
            'm_230919_135301_name' => 'name',
            'm_230919_162310_test_name' => 'test_name',
            'm_230919_162310_1' => '1',
        ];

        foreach ($items as $className => $name) {
            $migration = $this->getMigration($className);
            $this->assertSame($name, $migration->getName());
        }
    }

    public function testGetDate()
    {
        $items = [
            'm_230919_165614_test' => '2023-09-19 16:56:14',
            'm_230101_000000_test' => '2023-01-01 00:00:00',
        ];

        foreach ($items as $className => $date) {
            $migration = $this->getMigration($className);
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $this->assertEquals($dateTime, $migration->getDate());
        }
    }

    public function testGetHash()
    {
        $migration = $this->getMigration(self::CLASS_NAME);

        // В любой момент времени хэш должен быть одним и тем же
        $firstHash = $migration->getHash();
        sleep(2);
        $secondHash = $migration->getHash();

        $this->assertSame(32, strlen($firstHash));
        $this->assertSame($firstHash, $secondHash);
    }

    public function testIsApplied()
    {
        $db = $this->mock(Database::class, null, null, ['findByAttributes' => null]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertFalse($migration->isApplied());

        $db = $this->mock(Database::class, null, null, ['findByAttributes' => []]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertTrue($migration->isApplied());
    }

    public function testApply()
    {
        $db = $this->mock(Database::class, null, null, ['insert' => null]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertFalse($migration->apply());

        $db = $this->mock(Database::class, null, null, ['insert' => []]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertTrue($migration->apply());
    }

    public function testCancel()
    {
        $db = $this->mock(Database::class, null, null, ['delete' => true]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertTrue($migration->cancel());

        $db = $this->mock(Database::class, null, null, ['delete' => false]);
        $migration = $this->getMigration(self::CLASS_NAME, ['getDb' => $db]);
        $this->assertFalse($migration->cancel());
    }

    public function testCreate()
    {
        $items = [
            'name' => true, // Корректное название
            'na.me' => false, // Не соответствует шаблону
            'na|me' => false, // Недопустимый символ
        ];

        $temp = new Temp;
        $alias = '@test/temp';
        $manager = $this->getMigrationManager();

        foreach ($items as $name => $expected) {
            $actual = Migration::create($alias, $name);
            $this->assertSame($expected, $actual);

            if ($actual === false) {
                continue;
            }

            $migration = $manager->getLastMigration();
            $className = $migration->getClass();

            $this->assertFileContains(
                Alias::get("$alias/$className.php"),
                "class $className extends Migration"
            );

            $temp->clear();
        }
    }

    /**
     * @param string $class
     * @param array $methods
     * @return Migration
     */
    protected function getMigration(string $class, array $methods = []): object
    {
        $migrationManager = $this->getMigrationManager();
        return $this->mock(Migration::class, $class, [$migrationManager], $methods);
    }

    /**
     * @return MigrationManager
     */
    protected function getMigrationManager(): MigrationManager
    {
        $manager = new MigrationManager;
        $manager->alias = '@test/temp';
        return $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new Temp)->clear();
    }
}
