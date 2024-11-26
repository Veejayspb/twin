<?php

use twin\criteria\Criteria;
use twin\db\Database;
use test\helper\BaseTestCase;
use twin\migration\Migration;
use twin\model\Model;

final class DatabaseTest extends BaseTestCase
{
    public function testConstruct()
    {
        $code = $this->catchExceptionCode(function () {
            $database = $this->mock(Database::class, null, null, ['connect' => true]);
            $database->__construct();
        });

        $this->assertSame(0, $code);

        $code = $this->catchExceptionCode(function () {
            $database = $this->mock(Database::class, null, null, ['connect' => false]);
            $database->__construct();
        });

        $this->assertSame(500, $code);
    }

    public function testFind()
    {
        $criteria = $this->getCriteria();
        $db = $this->getDatabase();
        $result = $db->find($criteria);

        $this->assertSame(current($criteria->queryResult), $result);
    }

    public function testFindAll()
    {
        $criteria = $this->getCriteria();
        $db = $this->getDatabase();
        $result = $db->findAll($criteria);

        $this->assertSame($criteria->queryResult, $result);
    }

    /**
     * @return Criteria
     */
    private function getCriteria(): Criteria
    {
        return new class extends Criteria
        {
            public $queryResult = [
                [
                    'id' => 1,
                    'name' => 'first-name',
                ],
                [
                    'id' => 2,
                    'name' => 'second-name',
                ],
            ];

            public function query(Database $db): array
            {
                return $this->queryResult;
            }
        };
    }

    /**
     * @return Database
     */
    private function getDatabase(): Database
    {
        return new class extends Database
        {
            public function findAllByAttributes(string $table, array $attributes): array
            {
                return [];
            }

            public function findByAttributes(string $table, array $attributes): ?array
            {
                return null;
            }

            public function getPk(string $table): array
            {
                return [];
            }

            public function insertModel(Model $model): bool
            {
                return false;
            }

            public function updateModel(Model $model): bool
            {
                return false;
            }

            public function deleteModel(Model $model): bool
            {
                return false;
            }

            public function createMigrationTable(string $table): bool
            {
                return false;
            }

            public function isMigrationApplied(Migration $migration): bool
            {
                return false;
            }

            public function addMigration(Migration $migration): bool
            {
                return false;
            }

            public function deleteMigration(Migration $migration): bool
            {
                return false;
            }

            protected function connect(): bool
            {
                return true;
            }
        };
    }
}
