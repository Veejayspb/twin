<?php

namespace twin\controller;

use twin\common\Exception;
use twin\migration\MigrationManager;
use twin\Twin;

class MigrationController extends ConsoleController
{
    /**
     * Ссылка на список команд.
     */
    public function index()
    {
        $this->help();
    }

    /**
     * Список команд.
     */
    public function help()
    {
        echo 'help - reference' . PHP_EOL;
        echo 'create {name} - create new migration file' . PHP_EOL;
        echo 'apply {name} - apply all migrations up to specified (if name is empty, all migrations will be applied)';
    }

    /**
     * Создать новую миграцию.
     * @param string $name - название
     * @throws Exception
     */
    public function create($name)
    {
        $manager = Twin::app()->migration; /* @var MigrationManager $manager */
        if ($manager->create($name)) {
            echo 'Migration file was created';
        } else {
            throw new Exception(400, 'Error while creating migration file');
        }
    }

    /**
     * Применить миграцию.
     * Если указана конкретная миграция, то произойдет сдвиг до нее.
     * Если ничего не указано, то применятся все доступные миграции.
     * @param string|null $name - полное название
     */
    public function apply($name = null)
    {
        $manager = Twin::app()->migration; /* @var MigrationManager $manager */
        $manager->apply($name);
    }
}
