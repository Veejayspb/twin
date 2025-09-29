<?php

namespace twin\controller;

use twin\common\Exception;
use twin\migration\Migration;
use twin\Twin;

class MigrationController extends Controller
{
    /**
     * {@inheritdoc}
     */
    protected array $help = [
        'help - reference',
        'create {name} - create new migration',
        'status - show list of new migrations',
        'apply {name} - apply all migrations up to specified (if name is empty, all migrations will be applied)',
    ];

    /**
     * Ссылка на список команд.
     * @return array
     */
    public function actionIndex()
    {
        return $this->actionHelp();
    }

    /**
     * Список команд.
     * @return array
     */
    public function actionHelp()
    {
        return $this->help;
    }

    /**
     * Создать новую миграцию.
     * @param string $name - название миграции
     * @return array
     * @throws Exception
     */
    public function actionCreate($name)
    {
        if (!Twin::app()->migration->create($name)) {
            throw new Exception(400, 'Error while creating new migration file');
        }

        return ['Migration file was created'];
    }

    /**
     * Вывести список новых миграций.
     * @return array
     * @throws Exception
     */
    public function actionStatus()
    {
        $migrations = Twin::app()->migration->getNotAppliedMigrations();
        $count = count($migrations);
        $result = [];

        foreach ($migrations as $migration) {
            $result[] = $migration->getClass();
        }

        if ($count <= 1) {
            return ['No new migrations'];
        } else {
            return $result;
        }
    }

    /**
     * Накатить/откатить миграции.
     * Если указана конкретная миграция, то произойдет сдвиг до нее.
     * Если ничего не указано, то применятся все доступные миграции.
     * @param string|null $name
     * @return array
     * @throws Exception
     */
    public function actionApply($name = null)
    {
        $manager = Twin::app()->migration;

        if ($name === null) {
            $target = $manager->getLastMigration();
        } else {
            $target = $manager->findMigration($name);
        }

        if (!$target) {
            throw new Exception(400, "Migration not found: $name");
        }

        // Накат миграций
        $migrations = $manager->getMigrations();
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
            if (!$migration->getDate()->diff($target->getDate())->invert) {
                if ($migration->isApplied()) {
                    continue;
                }

                if ($migration->up() && $migration->apply()) {
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

            if ($migration->getDate()->diff($target->getDate())->invert) {
                if ($migration->down() && $migration->cancel()) {
                    $result[] = 'Migration down: ' . $migration->getClass();
                } else {
                    throw new Exception(500, 'Error while migrating down: ' . $migration->getClass());
                }
            }
        }

        return $result;
    }
}
