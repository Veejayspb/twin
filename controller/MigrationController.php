<?php

namespace twin\controller;

use twin\common\Exception;
use twin\migration\MigrationManager;
use twin\Twin;

class MigrationController extends ConsoleController
{
    /**
     * {@inheritdoc}
     */
    protected $help = [
        'help - reference',
        'current - current migration name and few before and after',
        'create {name} - create new migration file',
        'up - move to 1 migration up',
        'down - move to 1 migration down',
        'apply {name} - apply all migrations up to specified (if name is empty, all migrations will be applied)',
    ];

    /**
     * Компонент с менеджером миграций.
     * @var MigrationManager
     */
    protected $migration;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->migration = Twin::app()->migration;
    }

    /**
     * Вывести список миграций, в котором указана текущая, а также те, которые идут до и после.
     * @param int $dispersion - разброс
     */
    public function current($dispersion = 1)
    {
        $dispersion = (int)($dispersion < 1 ? 1 : $dispersion);
        $migrations = $this->migration->getMigrations(); // Список всех миграций
        $key = $this->migration->key(); // Индекс текущей миграции

        $result = [];
        for ($i = $key - $dispersion; $i <= $key + $dispersion; $i++) {
            if (!array_key_exists($i, $migrations)) continue;
            $name = $migrations[$i]->name;
            if ($i == $key) {
                $name .= ' <-';
            }
            $result[] = $name;
        }
        echo implode(PHP_EOL , $result);
    }

    /**
     * Создать новую миграцию.
     * @param string $name - название
     * @throws Exception
     */
    public function create($name)
    {
        if ($this->migration->create($name)) {
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
     * @throws Exception
     */
    public function apply($name = null)
    {
        $migration = $name === null ? $this->migration->getLast() : $this->migration->getByName($name);
        if (!$migration) throw new Exception(400, "Wrong migration name: $name");

        $current = $this->migration->current();

        if ($current->timestamp < $migration->timestamp) {
            while ($this->migration->current()->timestamp < $migration->timestamp) {
                $last = $this->migration->current();
                if (!$this->migration->up()) break;
                $current = $this->migration->current();
                echo "Migration up: $last->name -> $current->name" . PHP_EOL;
            }
        } elseif ($migration->timestamp < $current->timestamp) {
            while ($migration->timestamp < $this->migration->current()->timestamp) {
                $last = $this->migration->current();
                if (!$this->migration->down()) break;
                $current = $this->migration->current();
                echo "Migration down: $current->name <- $last->name" . PHP_EOL;
            }
        }
        echo 'Successfully migrated to: ' . $this->migration->current()->name;
    }

    /**
     * Миграция - шаг вперед.
     */
    public function up()
    {
        $last = $this->migration->current();
        if ($this->migration->up()) {
            $current = $this->migration->current();
            echo "Migration up: $last->name -> $current->name";
        } else {
            echo 'Migration failed';
        }
    }

    /**
     * Миграция - шаг назад.
     */
    public function down()
    {
        $last = $this->migration->current();
        if ($this->migration->down()) {
            $current = $this->migration->current();
            echo "Migration down: $current->name <- $last->name";
        } else {
            echo 'Migration failed';
        }
    }
}
