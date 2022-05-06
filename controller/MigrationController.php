<?php

namespace twin\controller;

use twin\common\Exception;
use twin\migration\Migration;
use twin\migration\MigrationManager;
use twin\Twin;

class MigrationController extends ConsoleController
{
    /**
     * {@inheritdoc}
     */
    protected $help = [
        'help - reference',
        'create {component} {name} - create new migration file',
        'status {component} - show the list of new migrations',
        'apply {component} {name} - apply all migrations up to specified (if name is empty, all migrations will be applied)',
    ];

    /**
     * Создать новую миграцию.
     * @param string $component - название компонента с БД
     * @param string $name - название миграции
     * @throws Exception
     */
    public function create($component, $name)
    {
        if ($this->getManager()->create($component, $name)) {
            echo 'Migration file was created';
        } else {
            throw new Exception(400, 'Error while creating new migration file');
        }
    }

    /**
     * Вывести список новых миграций.
     * @param string $component - название компонента с БД
     */
    public function status($component)
    {
        $migrations = $this->getManager()->getMigrations($component);
        $result = [];

        foreach ($migrations as $migration) {
            if ($migration->isApplied()) {
                continue;
            }
            $result[] = $migration->class;
        }

        if (empty($result)) {
            echo 'No new migrations';
        } else {
            echo 'New migrations:' . PHP_EOL;
            echo implode(PHP_EOL, $result);
        }
    }

    /**
     * Накатить/откатить миграции.
     * Если указана конкретная миграция, то произойдет сдвиг до нее.
     * Если ничего не указано, то применятся все доступные миграции.
     * @param string $component - название компонента с БД
     * @param string|null $name - полное название
     * @throws Exception
     */
    public function apply($component, $name = null)
    {
        $manager = $this->getManager();

        if (!$manager->isValidComponent($component)) {
            throw new Exception(400, "The component is not a database: $component");
        }

        // Целевая миграция, до которой обновляем
        if ($name === null) {
            $target = $manager->getLastMigration($component);
        } else {
            $target = $manager->findMigration($component, $name);
        }

        if (!$target) {
            throw new Exception(400, "Migration not found: $name");
        }

        // Накат миграций
        $migrations = $manager->getMigrations($component);
        $count = $this->applyUp($target, $migrations);

        // Откат миграций
        $migrationsReverse = array_reverse($migrations);
        $count+= $this->applyDown($target, $migrationsReverse);

        if ($count) {
            echo 'Successfully migrated to: ' . $target->class;
        } else {
            echo 'No migrations to apply';
        }
    }

    /**
     * Накат всех миграций до указанной.
     * @param Migration $target - целевая миграция, до которой накатываем
     * @param Migration[] $migrations - полный список миграций
     * @return int - кол-во примененных миграций
     * @throws Exception
     */
    protected function applyUp(Migration $target, array $migrations): int
    {
        $count = 0;

        foreach ($migrations as $migration) {
            if (!$migration->date->diff($target->date)->invert) {
                if ($migration->up()) {
                    $count++;
                    echo "Migration up: $migration->class " . PHP_EOL;
                } else {
                    throw new Exception(500, "Error while migrating up: $migration->class");
                }
            }
        }

        return $count;
    }

    /**
     * Откат всех миграций до указанной.
     * @param Migration $target - целевая миграция, до которой откатываем
     * @param Migration[] $migrations - полный список миграций
     * @return int
     * @throws Exception
     */
    protected function applyDown(Migration $target, array $migrations): int
    {
        $count = 0;

        foreach ($migrations as $migration) {
            if ($migration->date->diff($target->date)->invert) {
                if ($migration->down()) {
                    $count++;
                    echo "Migration down: $migration->class" . PHP_EOL;
                } else {
                    throw new Exception(500, "Error while migrating down: $migration->class");
                }
            }
        }

        return $count;
    }

    /**
     * Компонент с менеджером миграций.
     * @return MigrationManager
     */
    protected function getManager(): MigrationManager
    {
        return Twin::app()->migration;
    }
}
