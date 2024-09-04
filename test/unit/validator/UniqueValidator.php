<?php

namespace test\unit\validator;

use test\helper\BaseTestCase;
use test\helper\TestModel;
use twin\db\Database;
use twin\migration\Migration;
use twin\model\Model;
use twin\validator\Unique;

class UniqueValidator extends BaseTestCase
{
    const RECORDS = [
        [
            'id' => 1,
            'name' => 'name-1',
        ],
    ];

    public function testSimilar()
    {
        $items = [
            // Существующая запись, те же данные
            [
                'id' => 1,
                'name' => 'name-1',
                'expected' => true,
            ],
            // Существующая запись, изменение названия
            [
                'id' => 1,
                'name' => 'name-2',
                'expected' => true,
            ],
            // Новая запись, сохранение с названием, которое уже сущ-ет
            [
                'id' => 2,
                'name' => 'name-1',
                'expected' => false,
            ],
            // Новая запись, сохранение с названием, которое еще не сущ-ет
            [
                'id' => 2,
                'name' => 'name-2',
                'expected' => true,
            ],
        ];

        foreach ($items as $item) {
            $model = new TestModel;
            $model->id = $item['id'];
            $model->name = $item['name'];

            $validator = new Unique($model, ['name'], [
                'db' => $this->getDb(),
            ]);

            $this->assertSame(
                $item['expected'],
                $validator->similar('name')
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
            public function findByAttributes(string $table, array $attributes): ?array
            {
                foreach (UniqueValidator::RECORDS as $record) {
                    foreach ($attributes as $name => $value) {
                        if (!array_key_exists($name, $record) || $record[$name] != $value) {
                            continue 2;
                        }
                    }

                    return $record;
                }

                return null;
            }

            public function findAllByAttributes(string $table, array $attributes): array
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
