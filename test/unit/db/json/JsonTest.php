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

    public function testCreateModel()
    {
        $model = $this->getModel();
        $path = Alias::get('@test/temp/table.json');
        $db = new Json(self::CONFIG);

        $model->{Json::PK_FIELD} = 'hash';
        $model->id = 1;
        $result = $db->createModel($model);
        $data = json_decode(file_get_contents($path), true);
        $key = $model->{Json::PK_FIELD};

        $this->assertTrue($result);
        $this->assertArrayNotHasKey('hash', $data);
        $this->assertSame([$key => ['id' => 1]], $data);
    }

    public function testUpdateModel()
    {
        $path = Alias::get('@test/temp/table.json');
        $data = ['hash' => ['id' => 1]];
        file_put_contents($path, json_encode($data));
        $model = $this->getModel();
        $db = new Json(self::CONFIG);

        $model->{Json::PK_FIELD} = 'not-exists';
        $model->id = 2;
        $result = $db->updateModel($model);

        $this->assertFalse($result);

        $model->{Json::PK_FIELD} = 'hash';
        $result = $db->updateModel($model);
        $data = json_decode(file_get_contents($path), true);

        $this->assertTrue($result);
        $this->assertSame(['hash' => ['id' => 2]], $data);
    }

    public function testDeleteModel()
    {
        $path = Alias::get('@test/temp/table.json');
        $data = ['hash' => ['id' => 1]];
        file_put_contents($path, json_encode($data));
        $model = $this->getModel();
        $db = new Json(self::CONFIG);

        $model->{Json::PK_FIELD} = 'not-exists';
        $result = $db->deleteModel($model);

        $this->assertTrue($result);

        $model->{Json::PK_FIELD} = 'hash';
        $result = $db->deleteModel($model);
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
