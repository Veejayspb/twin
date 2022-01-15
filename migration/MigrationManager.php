<?php

namespace twin\migration;

use DirectoryIterator;
use Iterator;
use m_0_init;
use twin\common\Component;
use twin\Twin;

abstract class MigrationManager extends Component implements Iterator
{
    /**
     * Путь до директории с файлами миграций.
     * @var string
     */
    public $path = '@app/migration';

    /**
     * Индекс текущей миграции.
     * @var int
     */
    protected $index = 0;

    /**
     * Массив миграций.
     * @var Migration[]
     */
    protected $migrations = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->migrations = $this->getMigrations();
        $this->index = $this->getIndex();
    }

    /**
     * Создать новую миграцию.
     * @param string $name - название миграции
     * @return bool
     */
    public function create(string $name): bool
    {
        $path = Twin::getAlias($this->path);
        return Migration::create($path, $name);
    }

    /**
     * {@inheritdoc}
     * @return Migration|bool - FALSE, если миграций не сущ-ет
     */
    public function current()
    {
        if (!$this->valid()) return false;
        return $this->migrations[$this->index];
    }

    /**
     * {@inheritdoc}
     */
    public function previous()
    {
        if (0 < $this->index) {
            $this->index--;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->index, $this->migrations);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * Применить следующую миграцию.
     * @return bool
     */
    public function up(): bool
    {
        $this->next();

        $migration = $this->current();
        if ($migration === false || !$migration->up()) {
            $this->previous();
            return false;
        }

        $this->setTimestamp($migration->timestamp);
        return true;
    }

    /**
     * Отменить текущую миграцию.
     * @return bool
     */
    public function down(): bool
    {
        $migration = $this->current();

        if ($migration === false || !$migration->down()) {
            return false;
        }

        $this->previous();
        $migration = $this->current();

        if ($migration) {
            $this->setTimestamp($migration->timestamp);
        }
        return true;
    }

    /**
     * Вернуть миграцию по полному названию.
     * @param string $name - полное название миграции
     * @return Migration|bool - FALSE в случае ошибки
     */
    public function getByName(string $name)
    {
        foreach ($this->migrations as $item) {
            if ($name == $item->class) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Вернуть последнюю миграцию.
     * @return Migration
     */
    public function getLast(): Migration
    {
        return end($this->migrations);
    }

    /**
     * Вернуть миграции в хронологическом порядке (включая нулевую m_0_init).
     * @return Migration[]
     */
    public function getMigrations(): array
    {
        $dir = Twin::getAlias($this->path);
        $directoryIterator = new DirectoryIterator($dir);
        $result[] = $this->getInitMigration();

        foreach ($directoryIterator as $file) {
            if (!$file->isFile()) continue;
            require_once $file->getPathname();
            $class = str_replace('.php', '', $file->getFilename());
            $result[] = new $class;
        }
        return $result;
    }

    /**
     * Вернуть индекс последней миграции.
     * @return int
     */
    protected function getIndex(): int
    {
        $timestamp = $this->getTimestamp();

        foreach ($this->migrations as $index => $item) {
            if ($item->timestamp < $timestamp) continue;
            return $index;
        }
        return 0;
    }

    /**
     * Вернуть объект с базовой пустой миграцией.
     * @return Migration
     */
    protected function getInitMigration(): Migration
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'm_0_init.php';
        return new m_0_init;
    }

    /**
     * Сохранить дату текущей миграции из кеша.
     * @param int $timestamp - дата миграции
     * @return bool
     */
    abstract protected function setTimestamp(int $timestamp): bool;

    /**
     * Получить дату текущей миграции из кеша.
     * @return int
     */
    abstract protected function getTimestamp(): int;
}
