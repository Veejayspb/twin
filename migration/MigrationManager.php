<?php

namespace twin\migration;

use DirectoryIterator;
use Iterator;
use m_0_init;
use twin\common\Component;
use twin\Twin;

class MigrationManager extends Component implements Iterator
{
    /**
     * Файл для хранения данных по последней миграции.
     */
    const RUNTIME_STORAGE = '@runtime/migration.json';

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
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->createDir();
        $this->items = $this->getItems();
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
        return $this->items[$this->index];
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
        return array_key_exists($this->index, $this->items);
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
        foreach ($this->items as $item) {
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
        return end($this->items);
    }

    /**
     * Сохранить дату текущей миграции из кеша.
     * @param int $timestamp - дата миграции
     * @return bool
     */
    protected function setTimestamp(int $timestamp): bool
    {
        $path = Twin::getAlias(static::RUNTIME_STORAGE);
        $data = ['timestamp' => $timestamp];
        $content = json_encode($data);
        return (bool)file_put_contents($path, $content, LOCK_EX);
    }

    /**
     * Получить дату текущей миграции из кеша.
     * @return int
     */
    protected function getTimestamp(): int
    {
        $path = Twin::getAlias(static::RUNTIME_STORAGE);
        if (!is_file($path)) return 0;
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        return array_key_exists('timestamp', $data) ? (int)$data['timestamp'] : 0;
    }

    /**
     * Вернуть миграции в хронологическом порядке (включая нулевую m_0_init).
     * @return Migration[]
     */
    protected function getItems(): array
    {
        $dir = Twin::getAlias($this->path);
        $directoryIterator = new DirectoryIterator($dir);
        $result[] = $this->getInitMigration();
        foreach ($directoryIterator as $file) {
            if (!$file->isFile()) continue;
            require_once $dir . DIRECTORY_SEPARATOR . $file->getFilename();
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
        foreach ($this->items as $index => $item) {
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
        return new m_0_init();
    }

    /**
     * Создать директорию, если не сущ-ет.
     * @return bool
     */
    protected function createDir(): bool
    {
        $path = Twin::getAlias($this->path);
        if (!is_dir($path)) {
            return mkdir($path, 0775, true);
        }
        return true;
    }
}
