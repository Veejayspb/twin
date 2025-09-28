<?php

use PHPUnit\Framework\MockObject\MockObject;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use test\helper\BaseTestCase;
use test\helper\Temp;
use twin\Twin;

final class MigrationManagerTest extends BaseTestCase
{
    /**
     * Массив значений для генерации списка миграций.
     * ключ - название класса миграции
     * значение - значение, возвращаемое методом isApplied() (true/false)
     */
    const MIGRATIONS = [
        'm_231006_000000_first' => false,
        'm_231007_000000_second' => true,
        'm_231008_000000_third' => false,
        'm_231009_000000_231010_000000' => true,
        'm_231010_000000_fifth' => true,
    ];

    public function testCreate()
    {
        $items = [
            'name' => true, // Корректное название
            'na.me' => false, // Не соответствует шаблону
            'na|me' => false, // Недопустимый символ
        ];

        $manager = new MigrationManager;
        $manager->alias = '@test/temp';

        foreach ($items as $name => $expected) {
            $actual = $manager->create($name);
            $this->assertSame($expected, $actual);
        }
    }

    public function testGetMigrations()
    {
        $manager = new MigrationManager;
        $manager->alias = '@test/temp';
        $count = 3;

        // Создание миграций
        for ($i = 1; $i <= $count; $i++) {
            $manager->create('name' . $i);
        }

        $migrations = $manager->getMigrations();
        $this->assertSame($count + 1, count($migrations));

        // Проверка наличия всех миграций в массиве
        foreach ($migrations as $i => $migration) {
            if ($i === 0) {
                $this->assertSame(m_000000_000000_init::class, $migration->getClass());
            } else {
                $this->assertSame('name' . $i, $migration->getName());
            }
        }
    }

    public function testGetNotAppliedMigrations()
    {
        $manager = $this->getMigrationManager();
        $notApplied = $manager->getNotAppliedMigrations();

        $this->assertSame(2, count($notApplied));
        $this->assertArrayHasKey(0, $notApplied);
        $this->assertArrayHasKey(1, $notApplied);
        $this->assertSame('first', $notApplied[0]->getName());
        $this->assertSame('third', $notApplied[1]->getName());
    }

    public function testGetLastMigration()
    {
        $manager = $this->getMigrationManager();
        $migration = $manager->getLastMigration();

        $this->assertNotNull($migration);
        $this->assertSame('fifth', $migration->getName());
    }

    public function testFindMigration()
    {
        $manager = $this->getMigrationManager();

        $migration = $manager->findMigration('notexists');
        $this->assertNull($migration);

        $migration = $manager->findMigration('m_231006_000000_first');
        $this->assertNotNull($migration);
        $this->assertSame('first', $migration->getName());

        $migration = $manager->findMigration('second');
        $this->assertNotNull($migration);
        $this->assertSame('second', $migration->getName());

        $migration = $manager->findMigration('231008_000000');
        $this->assertNotNull($migration);
        $this->assertSame('third', $migration->getName());

        // Есть 2 миграции с данным названием: в дате и в имени
        $migration = $manager->findMigration('231010_000000');
        $this->assertNull($migration);
    }

    /**
     * @return MockObject|MigrationManager
     */
    protected function getMigrationManager(): MigrationManager
    {
        $properties = [
            'alias' => '@test/temp',
        ];

        $manager = $this->getMockBuilder(MigrationManager::class)
            ->setConstructorArgs([$properties])
            ->onlyMethods(['getMigrations'])
            ->getMock();

        Twin::import('@twin/migration/m_000000_000000_init.php', true);
        $migrations[] = new m_000000_000000_init($manager);

        foreach (self::MIGRATIONS as $className => $isApplied) {
            $migrations[] = $this->mock(Migration::class, $className, null, ['isApplied' => $isApplied]);
        }

        $manager
            ->expects($this->any())
            ->method('getMigrations')
            ->willReturn($migrations);

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

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        (new Temp)->clear();
    }
}
