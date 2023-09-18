<?php

namespace twin\migration;

use DirectoryIterator;
use m_000000_000000_init;
use twin\common\Component;
use twin\db\Database;
use twin\helper\Alias;
use twin\Twin;

class MigrationManager extends Component
{
    /**
     * Пути/алиасы директорий с файлами миграций.
     * @var array
     * ключ - название компонента с БД
     * значение - алиас директории с миграциями
     */
    public $paths = [];

    /**
     * Название таблицы с миграциями.
     * @var string
     */
    public $table = 'migration';

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['paths', 'table'];

    /**
     * Создать новую миграцию.
     * @param string $component - название компонента с БД
     * @param string $name - название миграции
     * @return bool
     */
    public function create(string $component, string $name): bool
    {
        $alias = $this->paths[$component] ?? null;

        if ($alias === null) {
            return false;
        }

        $path = Alias::get($alias);
        return Migration::create($path, $component, $name);
    }

    /**
     * Вернуть миграции в хронологическом порядке (включая нулевую m_000000_000000_init).
     * @param string $component - название компонента с БД
     * @return Migration[]
     */
    public function getMigrations(string $component): array
    {
        $result[] = $this->getInitMigration();

        if (!array_key_exists($component, $this->paths)) {
            return $result;
        }

        $path = Alias::get($this->paths[$component]);
        $migrations = $this->getMigrationsFromDir($path);

        // Исключить миграции, которые не относятся к указанному компоненту
        foreach ($migrations as $i => $migration) {
            if ($migration->component != $component) {
                unset($migrations[$i]);
            }
        }

        return array_merge($result, $migrations);
    }

    /**
     * Поиск миграции по названию.
     * @param string $component - название компонента с БД
     * @param string $name - название миграции
     * @return Migration|null
     */
    public function findMigration(string $component, string $name)
    {
        $migrations = $this->getMigrations($component);
        $byName = [];

        foreach ($migrations as $migration) {

            // Поиск по всему названию: m_000000_000000_name
            if ($name == $migration->getClass()) {
                return $migration;
            }

            // Поиск по дате: 000000_000000
            if ($name == $migration->date->format(Migration::DATE_FORMAT)) {
                return $migration;
            }

            // Поиск по названию, если оно уникальное: migration_name
            if ($name == $migration->getName()) {
                $byName[] = $migration;
            }
        }

        // Если миграция, найденная по названию, уникальна, то вернем ее
        if (count($byName) == 1) {
            return $byName[0];
        }

        return null;
    }

    /**
     * Вернуть последнюю по счету миграцию.
     * @param string $component - название компонента с БД
     * @return Migration
     */
    public function getLastMigration(string $component): Migration
    {
        $migrations = $this->getMigrations($component);
        $key = array_key_last($migrations);
        return $migrations[$key];
    }

    /**
     * Проверить, является ли компонент базой данных.
     * @param string $name - название компонента
     * @return bool
     */
    public function isValidComponent(string $name): bool
    {
        $component = Twin::app()->$name;
        return $component !== null && is_subclass_of($component, Database::class);
    }

    /**
     * Вернуть объект с базовой пустой миграцией.
     * @return Migration
     */
    protected function getInitMigration(): Migration
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'm_000000_000000_init.php';
        return new m_000000_000000_init($this);
    }

    /**
     * Вернуть все миграции из директории.
     * @param string $path - путь до директории
     * @return Migration[]
     */
    private function getMigrationsFromDir(string $path): array
    {
        $directoryIterator = new DirectoryIterator($path);
        $result = [];

        foreach ($directoryIterator as $file) {
            // Если является директорией
            if (!$file->isFile()) {
                continue;
            }

            require_once $file->getPathname();
            $class = str_replace('.php', '', $file->getFilename());

            // Если не является миграцией
            if (!is_subclass_of($class, Migration::class)) {
                continue;
            }

            $result[] = new $class($this);
        }

        return $result;
    }
}
