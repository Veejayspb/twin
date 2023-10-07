<?php

namespace twin\migration;

use DirectoryIterator;
use m_000000_000000_init;
use twin\common\Component;
use twin\helper\Alias;
use twin\Twin;

class MigrationManager extends Component
{
    /**
     * Алиас пути директории с файлами миграций.
     * @var string
     */
    public $alias = '@self/migration';

    /**
     * Название таблицы с миграциями.
     * @var string
     */
    public $table = 'migration';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['alias', 'table'];

    /**
     * Создать новую миграцию.
     * @param string $name - название миграции
     * @return bool
     */
    public function create(string $name): bool
    {
        return Migration::create($this->alias, $name);
    }

    /**
     * Вернуть миграции в хронологическом порядке (включая нулевую m_000000_000000_init).
     * @return Migration[]
     */
    public function getMigrations(): array
    {
        $result[] = $this->getInitMigration();
        $path = Alias::get($this->alias);
        $directoryIterator = new DirectoryIterator($path);

        foreach ($directoryIterator as $file) {
            // Если является директорией
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getRealPath();
            $migration = $this->getMigrationByPath($path);

            if (!$migration) {
                continue;
            }

            $result[] = $migration;
        }

        return $result;
    }

    /**
     * Вернуть не примененные миграции.
     * @return Migration[]
     */
    public function getNotAppliedMigrations(): array
    {
        $migrations = $this->getMigrations();

        $migrations = array_filter($migrations, function (Migration $migration) {
            return !$migration->isApplied();
        });

        return array_values($migrations);
    }

    /**
     * Вернуть последнюю по счету миграцию.
     * @return Migration|null
     */
    public function getLastMigration(): ?Migration
    {
        $migrations = $this->getMigrations();
        $key = array_key_last($migrations);
        return $key === null ? null : $migrations[$key];
    }

    /**
     * Поиск миграции по названию.
     * @param string $name - название миграции
     * @return Migration|null
     */
    public function findMigration(string $name): ?Migration
    {
        $migrations = $this->getMigrations();
        $result = [];

        foreach ($migrations as $migration) {

            // Поиск по всему названию: m_000000_000000_name
            if ($name == $migration->getClass()) {
                return $migration;
            }

            // Поиск по дате: 000000_000000
            if ($name == $migration->getDate()->format(Migration::DATE_FORMAT)) {
                $result[] = $migration;
            }

            // Поиск по названию: migration_name
            if ($name == $migration->getName()) {
                $result[] = $migration;
            }
        }

        // Если миграция, найденная по названию, уникальна, то вернем ее
        if (count($result) == 1) {
            return current($result);
        }

        return null;
    }

    /**
     * Вернуть объект с базовой пустой миграцией.
     * @return Migration
     */
    protected function getInitMigration(): Migration
    {
        Twin::import('@twin/migration/m_000000_000000_init.php', true);
        return new m_000000_000000_init($this);
    }

    /**
     * Вернуть миграцию по указанному пути.
     * @param string $path - путь до файла с миграцией
     * @return Migration|null
     */
    private function getMigrationByPath(string $path): ?Migration
    {
        Twin::import($path, true);
        $fileName = basename($path);
        $class = str_replace('.php', '', $fileName);

        // Если класс не сущ-ет или не является миграцией
        if (!is_subclass_of($class, Migration::class)) {
            return null;
        }

        return new $class($this);
    }
}
