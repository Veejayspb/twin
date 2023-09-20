<?php

use twin\helper\Alias;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use twin\test\helper\BaseTestCase;
use twin\test\helper\Database;
use twin\test\helper\ObjectProxy;
use twin\test\helper\Temp;

final class MigrationTest extends BaseTestCase
{
    /**
     * @var Migration
     */
    private $mock;

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

        $migrationManager = $this->getMigrationManager();

        foreach ($items as $className => $expectCode) {
            $this->mock = null;

            $code = $this->catchExceptionCode(function () use ($className, $migrationManager) {
                \twin\test\helper\Migration::$_returnValues['getClass'] = $className;
                $this->mock = new \twin\test\helper\Migration($migrationManager);
            });

            if ($code === 0) {
                $proxy = new ObjectProxy($this->mock);
                $this->assertSame($migrationManager, $proxy->manager);
            }

            $this->assertSame($expectCode, $code);
        }
    }

    public function testCall()
    {
        $up = [
            [
                'isApplied' => true,
                'up' => true,
                'expected' => true,
            ],
            [
                'isApplied' => true,
                'up' => false,
                'expected' => true,
            ],
            [
                'isApplied' => false,
                'up' => true,
                'expected' => true,
            ],
            [
                'isApplied' => false,
                'up' => false,
                'expected' => false,
            ],
        ];

        $down = [
            [
                'isApplied' => true,
                'down' => true,
                'expected' => true,
            ],
            [
                'isApplied' => true,
                'down' => false,
                'expected' => false,
            ],
            [
                'isApplied' => false,
                'down' => true,
                'expected' => true,
            ],
            [
                'isApplied' => false,
                'down' => false,
                'expected' => true,
            ],
        ];

        $migrationManager = $this->getMigrationManager();
        $migration = new \twin\test\helper\Migration($migrationManager);

        // Применение миграции (UP)
        foreach ($up as $item) {
            \twin\test\helper\Migration::$_returnValues['up'] = $item['up'];
            \twin\test\helper\Migration::$_returnValues['isApplied'] = $item['isApplied'];

            $this->assertSame($item['expected'], $migration->up());
        }

        // Откат миграции (DOWN)
        foreach ($down as $item) {
            \twin\test\helper\Migration::$_returnValues['down'] = $item['down'];
            \twin\test\helper\Migration::$_returnValues['isApplied'] = $item['isApplied'];

            $this->assertSame($item['expected'], $migration->down());
        }
    }

    public function testGetClass()
    {
        $migrationManager = $this->getMigrationManager();
        $className = 'm_230919_135301_test_name';

        $mock = $this->getMockForAbstractClass(
            Migration::class,
            [$migrationManager],
            $className
        );

        $this->assertSame($className, $mock->getClass());
    }

    public function testGetName()
    {
        $items = [
            'm_230919_135301_name' => 'name',
            'm_230919_162310_test_name' => 'test_name',
            'm_230919_162310_1' => '1',
        ];

        $migrationManager = $this->getMigrationManager();

        foreach ($items as $className => $name) {
            $mock = $this->getMockForAbstractClass(
                Migration::class,
                [$migrationManager],
                $className
            );

            $this->assertSame($name, $mock->getName());
        }
    }

    public function testGetDate()
    {
        $items = [
            'm_230919_165614_test' => '2023-09-19 16:56:14',
            'm_230101_000000_test' => '2023-01-01 00:00:00',
        ];

        $migrationManager = $this->getMigrationManager();

        foreach ($items as $className => $date) {
            $mock = $this->getMockForAbstractClass(
                Migration::class,
                [$migrationManager],
                $className
            );

            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $this->assertEquals($dateTime, $mock->getDate());
        }
    }

    public function testGetManager()
    {
        $migrationManager = $this->getMigrationManager();
        $migration = new \twin\test\helper\Migration($migrationManager);
        $proxy = new ObjectProxy($migration);

        $this->assertSame($migrationManager, $proxy->manager);
    }

    public function testIsApplied()
    {
        $migrationManager = $this->getMigrationManager();
        $migration = new \twin\test\helper\Migration($migrationManager);

        Database::$_returnValues['isMigrationApplied'] = true;
        $actual = $migration->isApplied();

        $this->assertTrue($actual);

        Database::$_returnValues['isMigrationApplied'] = false;
        $actual = $migration->isApplied();

        $this->assertFalse($actual);
    }

    public function testGetHash()
    {
        $migrationManager = $this->getMigrationManager();
        $migration = new \twin\test\helper\Migration($migrationManager);

        // В любой момент времени хэш должен быть одним и тем же
        $firstHash = $migration->getHash();
        sleep(2);
        $secondHash = $migration->getHash();

        $this->assertIsString($firstHash);
        $this->assertSame($firstHash, $secondHash);
    }

    public function testCreate()
    {
        $items = [
            'name' => true,
            'na|me' => false,
        ];

        $temp = new Temp;

        $path = '@twin/test/temp';
        $component = 'db';

        foreach ($items as $name => $expected) {
            $temp->clear();

            $actual = Migration::create($path, $component, $name);
            $filePath = $this->getMigrationPath($name);
            $className = basename($filePath, '.php');

            $this->assertSame($expected, $actual);
            $expected ? $this->assertFileExists($filePath) : $this->assertNull($filePath);

            $this->assertFileContains(
                $filePath ?: '',
                "protected \$component = '$component';"
            );

            $this->assertFileContains(
                $filePath ?: '',
                "class $className extends Migration"
            );
        }

        $temp->clear();
    }

    /**
     * @return MigrationManager
     */
    protected function getMigrationManager(): MigrationManager
    {
        $paths = ['db' => __DIR__];
        return new MigrationManager(['paths' => $paths]);
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function getMigrationPath(string $name): ?string
    {
        for ($i = -10; $i <= 10; $i++) {
            $timestamp = time();
            $date = date(Migration::DATE_FORMAT, $timestamp + $i);
            $fileName = Migration::PREFIX . $date . '_' . $name . '.php';
            $path = Alias::get("@twin/test/temp/$fileName");

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        \twin\test\helper\Migration::$_returnValues = [];
        Database::$_returnValues = [];
    }
}
