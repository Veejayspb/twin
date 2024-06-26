<?php

use twin\db\json\Json;
use twin\helper\Alias;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use test\helper\BaseTestCase;
use test\helper\Temp;

final class JsonTest extends BaseTestCase
{
    const CONFIG = [
        'alias' => '@test',
        'dbname' => 'temp',
    ];

    public function testGetData()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/table.json');
        $data = ['key' => 'value'];

        $this->assertSame([], $db->getData('table'));
        file_put_contents($path, json_encode($data), LOCK_EX);
        $this->assertSame($data, $db->getData('table'));
    }

    public function testSetData()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/table.json');
        $data = ['key' => 'value'];

        $this->assertFileDoesNotExist($path);
        $result = $db->setData('table', $data);
        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame($data, json_decode(file_get_contents($path), true));
    }

    public function testCreateMigrationTable()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/migration.json');

        $this->assertFileDoesNotExist($path);
        $result = $db->createMigrationTable('migration');
        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame([], json_decode(file_get_contents($path), true));
    }

    public function testIsMigrationApplied()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/migration.json');

        $migration = $this->getMigration('m_231010_044000_name1');
        file_put_contents($path, json_encode([
            $migration->getHash() => [],
        ]), LOCK_EX);
        $result = $db->isMigrationApplied($migration);
        $this->assertTrue($result);

        $migration = $this->getMigration('m_231010_044000_name2');
        $result = $db->isMigrationApplied($migration);
        $this->assertFalse($result);
    }

    public function testAddMigration()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/migration.json');
        $className = 'm_231010_045000_name';

        $migration = $this->getMigration($className);
        $result = $db->addMigration($migration);
        $this->assertTrue($result);
        $this->assertFileExists($path);

        $data = json_decode(file_get_contents($path), true);
        $first = current($data);
        $this->assertSame(1, count($data));
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('timestamp', $first);
        $this->assertSame($className, $first['name']);
        $this->assertIsInt($first['timestamp']);
        $this->assertTrue($first['timestamp'] <= time());
    }

    public function testDeleteMigration()
    {
        $db = new Json(self::CONFIG);
        $path = Alias::get('@test/temp/migration.json');
        $migration1 = $this->getMigration('m_231010_045000_name1');
        $migration2 = $this->getMigration('m_231010_045000_name2');

        // Добавить миграцию и проверить, что она есть
        $db->addMigration($migration1);
        $data = json_decode(file_get_contents($path), true);
        $this->assertSame(1, count($data));

        // Попытка удалить миграцию, которой нет в списке примененных
        $result = $db->deleteMigration($migration2);
        $this->assertTrue($result);
        $data = json_decode(file_get_contents($path), true);
        $this->assertSame(1, count($data));

        // Удаление примененной миграции
        $result = $db->deleteMigration($migration1);
        $this->assertTrue($result);
        $data = json_decode(file_get_contents($path), true);
        $this->assertSame(0, count($data));
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
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        (new Temp)->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        (new Temp)->clear();
    }
}
