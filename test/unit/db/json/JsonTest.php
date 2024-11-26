<?php

use twin\db\json\Json;
use twin\helper\Alias;
use test\helper\BaseTestCase;
use test\helper\Temp;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use twin\model\Model;

final class JsonTest extends BaseTestCase
{
    const CONFIG = [
        'alias' => '@test',
        'dbname' => 'temp',
    ];

    const DB_DATA = [
        'a' => ['name' => 'vasya', 'type_id' => 1],
        'b' => ['name' => 'peter', 'type_id' => 1],
        'c' => ['name' => 'ville', 'type_id' => 2],
    ];

    public function testInsert()
    {
        $path = Alias::get('@test/temp/table.json');
        $db = new Json(self::CONFIG);
        $row = ['id' => 1];
        $result = $db->insert('table', $row);
        $data = json_decode(file_get_contents($path), true);

        $this->assertIsString($result);
        $this->assertFileExists($path);
        $this->assertArrayNotHasKey(Json::PK_FIELD, current($data));
        $this->assertSame($row, current($data));
    }

    public function testUpdate()
    {
        $path = Alias::get('@test/temp/table.json');
        $data = ['hash' => ['id' => 1]];
        file_put_contents($path, json_encode($data));
        $db = new Json(self::CONFIG);

        $result = $db->update('table', ['id' => 2], 'not-exists');

        $this->assertFalse($result);
        $this->assertSame($data, ['hash' => ['id' => 1]]);

        $result = $db->update('table', ['id' => 2], 'hash');
        $data = json_decode(file_get_contents($path), true);

        $this->assertTrue($result);
        $this->assertCount(1, $data);
        $this->assertSame(['hash' => ['id' => 2]], $data);
    }

    public function testDelete()
    {
        $path = Alias::get('@test/temp/table.json');
        $data = ['hash' => ['id' => 1]];
        file_put_contents($path, json_encode($data));
        $db = new Json(self::CONFIG);

        $result = $db->delete('table', 'no-exists');
        $data = json_decode(file_get_contents($path), true);

        $this->assertTrue($result);
        $this->assertSame(['hash' => ['id' => 1]], $data);

        $result = $db->delete('table', 'hash');
        $data = json_decode(file_get_contents($path), true);

        $this->assertTrue($result);
        $this->assertSame([], $data);
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

    public function testFindAllByAttributes()
    {
        $db = $this->getDatabase();

        $rows = $db->findAllByAttributes('test', ['name' => 'vasya']);
        $this->assertSame([['name' => 'vasya', 'type_id' => 1, Json::PK_FIELD => 'a']], $rows);

        $rows = $db->findAllByAttributes('test', ['name' => 'undefined']);
        $this->assertSame([], $rows);

        $rows = $db->findAllByAttributes('test', ['type_id' => 2]);
        $this->assertSame([['name' => 'ville', 'type_id' => 2, Json::PK_FIELD => 'c']], $rows);

        $rows = $db->findAllByAttributes('test', ['type_id' => '1']);
        $this->assertSame([], $rows);
    }

    public function testFindByAttributes()
    {
        $db = $this->getDatabase();

        $row = $db->findByAttributes('test', ['name' => 'vasya']);
        $this->assertSame(['name' => 'vasya', 'type_id' => 1, Json::PK_FIELD => 'a'], $row);

        $row = $db->findByAttributes('test', ['name' => 'undefined']);
        $this->assertNull($row);

        $row = $db->findByAttributes('test', ['type_id' => 2]);
        $this->assertSame(['name' => 'ville', 'type_id' => 2, Json::PK_FIELD => 'c'], $row);

        $row = $db->findByAttributes('test', ['type_id' => '1']);
        $this->assertSame(null, $row);
    }

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
     * @return Model
     */
    protected function getModel(): Model
    {
        return new class extends Model
        {
            public static function tableName(): string
            {
                return 'table';
            }

            protected function attributeNames(): array
            {
                return [Json::PK_FIELD, 'id'];
            }
        };
    }

    /**
     * @return Json
     */
    protected function getDatabase(): Json
    {
        return new class extends Json
        {
            public function __construct(array $properties = []) {}

            public function getData(string $table): array
            {
                return JsonTest::DB_DATA;
            }

            public function connect(): bool
            {
                return true;
            }
        };
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
