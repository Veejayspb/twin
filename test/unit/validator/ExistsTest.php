<?php

namespace test\unit\validator;

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\db\Database;
use twin\migration\Migration;
use twin\model\Model;
use twin\validator\Exists;

class ExistsTest extends BaseTestCase
{
    const IDS_EXISTS = [1, 2];

    public function testExists()
    {
        $items = [
            [
                'id' => 1,
                'expected' => true,
            ],
            [
                'id' => 3,
                'expected' => false,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->id = $item['id'];

            $validator = new Exists($model, ['id'], [
                'db' => $this->getDb(),
                'table' => 'test',
                'conditions' => ['id' => $model->id],
            ]);

            $this->assertSame(
                $item['expected'],
                $validator->exists('id')
            );
        }
    }

    /**
     * @return Database
     */
    private function getDb(): Database
    {
        return new class extends Database
        {
            public function findByAttributes(string $table, array $conditions): ?array
            {
                $id = $conditions['id'] ?? 0;
                return in_array($id, ExistsTest::IDS_EXISTS) ? [] : null;
            }

            public function findAllByAttributes(string $table, array $conditions): array
            {
                return [];
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
