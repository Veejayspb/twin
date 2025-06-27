<?php

use test\helper\BaseTestCase;
use test\helper\Temp;
use twin\db\Json;

final class JsonTest extends BaseTestCase
{
    const TABLE = 'table';
    const USER_1 = [
        'id' => 1,
        'age' => 25,
        'name' => 'user-1',
    ];
    const USER_2 = [
        'id' => 2,
        'age' => 25,
        'name' => 'user-2',
    ];
    const USER_3 = [
        'id' => 3,
        'age' => 31,
        'name' => 'user-3',
    ];

    public function testGetPk()
    {
        $json = $this->getJson();

        $actual = $json->getPk(self::TABLE);
        $this->assertSame([$json->pkField], $actual);

        $json->pkField = 'test';
        $actual = $json->getPk(self::TABLE);
        $this->assertSame([$json->pkField], $actual);
    }

    public function testGetTables()
    {
        $json = $this->getJson();

        $actual = $json->getTables();
        $this->assertSame([], $actual);

        $json->createTable('user');
        $actual = $json->getTables();
        $this->assertSame(['user'], $actual);

        $json->createTable('log');
        $actual = $json->getTables();
        $this->assertSame(['log', 'user'], $actual);
    }

    public function testCreateTable()
    {
        $json = $this->getJson();

        $actual = $json->createTable('USER');
        $this->assertFalse($actual);
        $this->assertSame([], $json->getTables());

        $actual = $json->createTable('user_1');
        $this->assertTrue($actual);
        $this->assertSame(['user_1'], $json->getTables());
    }

    public function testDeleteTable()
    {
        $json = $this->getJson();
        $json->createTable('user');
        $json->createTable('log');

        $actual = $json->deleteTable('USER');
        $this->assertFalse($actual);
        $this->assertSame(['log', 'user'], $json->getTables());

        $actual = $json->deleteTable('user');
        $this->assertTrue($actual);
        $this->assertSame(['log'], $json->getTables());

        $actual = $json->deleteTable('log');
        $this->assertTrue($actual);
        $this->assertSame([], $json->getTables());
    }

    public function testFindAllByAttributes()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $json->insert(self::TABLE, self::USER_1);
        $json->insert(self::TABLE, self::USER_2);
        $json->insert(self::TABLE, self::USER_3);

        $rows = $json->findAllByAttributes(self::TABLE, ['id' => 1]);
        $this->assertSame([self::USER_1], array_values($rows));

        $rows = $json->findAllByAttributes(self::TABLE, ['id' => 77]);
        $this->assertSame([], array_values($rows));

        $rows = $json->findAllByAttributes(self::TABLE, ['id' => 1, 'age' => 2]);
        $this->assertSame([], array_values($rows));

        $rows = $json->findAllByAttributes(self::TABLE, ['undefined' => 1]);
        $this->assertSame([], array_values($rows));

        $rows = $json->findAllByAttributes(self::TABLE, ['age' => 25]);
        $this->assertSame([self::USER_1, self::USER_2], array_values($rows));
    }

    public function testFindByAttributes()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $json->insert(self::TABLE, self::USER_1);
        $json->insert(self::TABLE, self::USER_2);
        $json->insert(self::TABLE, self::USER_3);

        $actual = $json->findByAttributes(self::TABLE, ['id' => 1]);
        $this->assertSame(self::USER_1, $actual);

        $actual = $json->findByAttributes(self::TABLE, ['id' => 77]);
        $this->assertNull($actual);

        $actual = $json->findByAttributes(self::TABLE, ['id' => 1, 'age' => 2]);
        $this->assertNull($actual);

        $actual = $json->findByAttributes(self::TABLE, ['undefined' => 1]);
        $this->assertNull($actual);

        $actual = $json->findByAttributes(self::TABLE, ['age' => 25]);
        $this->assertSame(self::USER_1, $actual);
    }

    public function testInsert()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);

        $json->insert(self::TABLE, self::USER_1);
        $data = $json->getData(self::TABLE);
        $expected[] = self::USER_1;
        $this->assertSame($expected, array_values($data));

        $json->insert(self::TABLE, self::USER_2);
        $data = $json->getData(self::TABLE);
        $expected[] = self::USER_2;
        $this->assertSame($expected, array_values($data));

        $json->insert(self::TABLE, self::USER_2);
        $data = $json->getData(self::TABLE);
        $expected[] = self::USER_2;
        $this->assertSame($expected, array_values($data));
    }

    public function testUpdate()
    {
        $user_1 = self::USER_1;
        $user_2 = self::USER_2;
        $user_3 = self::USER_3;

        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $json->setData(self::TABLE, [$user_1, $user_2, $user_3]);

        // Изменить несуществующую запись
        $result = $json->update(self::TABLE, ['age' => 1], ['id' => 77]);
        $this->assertTrue($result);
        $data = $json->getData(self::TABLE);
        $this->assertSame([$user_1, $user_2, $user_3], array_values($data));

        // Изменить возраст
        $result = $json->update(self::TABLE, ['age' => 1], ['id' => 3]);
        $this->assertTrue($result);
        $user_3 = ['age' => 1] + $user_3;
        $data = $json->getData(self::TABLE);
        $this->assertSame([$user_1, $user_2, $user_3], array_values($data));

        // Изменить несуществующий ранее атрибут
        $result = $json->update(self::TABLE, ['undefined' => 'str'], ['id' => 3]);
        $this->assertTrue($result);
        $user_3 = ['undefined' => 'str'] + $user_3;
        $data = $json->getData(self::TABLE);
        $this->assertSame([$user_1, $user_2, $user_3], array_values($data));

        // Изменить несколько записей
        $result = $json->update(self::TABLE, ['age' => 36], ['age' => 25]);
        $this->assertTrue($result);
        $user_1 = ['age' => 36] + $user_1;
        $user_2 = ['age' => 36] + $user_2;
        $data = $json->getData(self::TABLE);
        $this->assertSame([$user_1, $user_2, $user_3], array_values($data));
    }

    public function testDelete()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $json->insert(self::TABLE, self::USER_1);
        $json->insert(self::TABLE, self::USER_2);
        $json->insert(self::TABLE, self::USER_3);

        // Удаление нескольких записей
        $actual = $json->delete(self::TABLE, ['age' => 25]);
        $this->assertTrue($actual);
        $rows = $json->getData(self::TABLE);
        $this->assertSame([self::USER_3], array_values($rows));

        // Удаление несуществующей записи
        $actual = $json->delete(self::TABLE, ['id' => 4]);
        $this->assertTrue($actual);
        $rows = $json->getData(self::TABLE);
        $this->assertSame([self::USER_3], array_values($rows));

        // Удаление одной записи
        $actual = $json->delete(self::TABLE, ['id' => 3]);
        $this->assertTrue($actual);
        $rows = $json->getData(self::TABLE);
        $this->assertSame([], array_values($rows));
    }

    public function testGetData()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $path = $json->getFilePath(self::TABLE);

        $actual = $json->getData(self::TABLE);
        $this->assertSame([], $actual);

        $data = [1, 2, 3];
        file_put_contents($path, json_encode($data));
        $actual = $json->getData(self::TABLE);
        $this->assertSame($data, $actual);
    }

    public function testSetData()
    {
        $json = $this->getJson();
        $json->createTable(self::TABLE);
        $path = $json->getFilePath(self::TABLE);

        $content = file_get_contents($path);
        $this->assertSame(json_encode([]), $content);

        $data = [1, 2, 3];
        $actual = $json->setData(self::TABLE, $data);
        $this->assertTrue($actual);
        $content = file_get_contents($path);
        $this->assertSame(json_encode($data), $content);
    }

    /**
     * @return Json
     */
    protected function getJson()
    {
        return new class extends Json
        {
            public string $dbName = 'db';
            public string $alias = '@test/temp';

            public function getFilePath(string $table): string
            {
                return parent::getFilePath($table);
            }
        };
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
