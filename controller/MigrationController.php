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
     * @return array
     * @throws Exception
     */
    public function create($component, $name)
    {
        if ($this->getManager()->create($component, $name)) {
            return ['Migration file was created'];
        } else {
            throw new Exception(400, 'Error while creating new migration file');
        }
    }

    /**
     * Вывести список новых миграций.
     * @param string $component - название компонента с БД
     * @return array
     */
    public function status($component)
    {
        $migrations = $this->getManager()->getMigrations($component);
        $result = ['New migrations:'];

        foreach ($migrations as $migration) {
            if ($migration->isApplied()) continue;
            $result[] = $migration->getClass();
        }

        if (empty($result)) {
            return ['No new migrations'];
        } else {
            return $result;
        }
    }

    /**
     * Накатить/откатить миграции.
     * Если указана конкретная миграция, то произойдет сдвиг до нее.
     * Если ничего не указано, то применятся все доступные миграции.
     * @param string $component - название компонента с БД
     * @param string|null $name - полное название
     * @return array
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
        $up = $this->applyUp($target, $migrations);

        // Откат миграций
        $migrationsReverse = array_reverse($migrations);
        $down = $this->applyDown($target, $migrationsReverse);

        $result = array_merge($up, $down);

        if ($result) {
            $result[] = 'Successfully migrated to: ' . $target->getClass();
        } else {
            $result[] = 'No migrations to apply';
        }

        return $result;
    }

    /**
     * Накат всех миграций до указанной.
     * @param Migration $target - целевая миграция, до которой накатываем
     * @param Migration[] $migrations - полный список миграций
     * @return array
     * @throws Exception
     */
    protected function applyUp(Migration $target, array $migrations): array
    {
        $result = [];

        foreach ($migrations as $migration) {
            if (!$migration->date->diff($target->date)->invert) {

                if ($migration->isApplied()) {
                    continue;
                }

                if ($migration->up()) {
                    $result[] = 'Migration up: ' . $migration->getClass();
                } else {
                    throw new Exception(500, 'Error while migrating up: ' . $migration->getClass());
                }
            }
        }

        return $result;
    }

    /**
     * Откат всех миграций до указанной.
     * @param Migration $target - целевая миграция, до которой откатываем
     * @param Migration[] $migrations - полный список миграций
     * @return array
     * @throws Exception
     */
    protected function applyDown(Migration $target, array $migrations): array
    {
        $result = [];

        foreach ($migrations as $migration) {

            if (!$migration->isApplied()) {
                continue;
            }

            if ($migration->date->diff($target->date)->invert) {
                if ($migration->down()) {
                    $result[] = 'Migration down: ' . $migration->getClass();
                } else {
                    throw new Exception(500, 'Error while migrating down: ' . $migration->getClass());
                }
            }
        }

        return $result;
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
