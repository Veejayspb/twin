<?php

use test\helper\TestModel;
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

    public function testCreateModel()
    {
        $model = new TestModel;
        $tableName = TestModel::tableName();
        $json = new Json(self::CONFIG);

        // Добавление модели
        $model->id = 1;
        $result = $json->createModel($model);
        $data['d2ce28b9a7fd7e4407e2b0fd499b7fe4'] = ['id' => 1, 'name' => null];
        $this->assertTrue($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );

        // Добавление модели с уже существующим ПК
        $result = $json->createModel($model);
        $this->assertFalse($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );
    }

    public function testUpdateModel()
    {
        $model = new TestModel;
        $tableName = TestModel::tableName();
        $data = [
            'd2ce28b9a7fd7e4407e2b0fd499b7fe4' => ['id' => 1, 'name' => 'old-name'],
        ];

        $json = new Json(self::CONFIG);
        $json->setData($tableName, $data);

        // Изменение существующей модели
        $model->id = 1;
        $model->name = 'new-name';
        $result = $json->updateModel($model);
        $data['d2ce28b9a7fd7e4407e2b0fd499b7fe4'] = ['id' => 1, 'name' => 'new-name'];
        $this->assertTrue($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );

        // Попытка изменения несуществующей модели
        $model->id = 123;
        $result = $json->updateModel($model);
        $this->assertFalse($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );
    }

    public function testDeleteModel()
    {
        $model = new TestModel;
        $tableName = TestModel::tableName();
        $data = [
            'd2ce28b9a7fd7e4407e2b0fd499b7fe4' => ['id' => 1, 'name' => 'some-name'],
            '4f56edcb1558d4df2f77295f86059006' => ['id' => 2, 'name' => null],
        ];

        $json = new Json(self::CONFIG);
        $json->setData($tableName, $data);

        // Удаление модели с существующим ПК
        $model->id = 2;
        $result = $json->deleteModel($model);
        unset($data['4f56edcb1558d4df2f77295f86059006']);
        $this->assertTrue($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );

        // Удаление модели с несуществующим ПК
        $model->id = 123;
        $result = $json->deleteModel($model);
        $this->assertFalse($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );

        // Удаление модели с ПК, который совпадает по значению, но не совпадает по типу: 1 и "1"
        $model->id = '1';
        $result = $json->deleteModel($model);
        $this->assertFalse($result);
        $this->assertSame(
            $data,
            $json->getData($tableName)
        );

        // Попытка удаления при отсутствующем ПК
        $model->_pk = [];
        $result = $json->deleteModel($model);
        $this->assertFalse($result);
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
