<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;

abstract class Database extends Component
{
    /**
     * Название базы данных.
     * @var string
     */
    public string $dbName;

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['dbName'];

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (!$this->connect()) {
            throw new Exception(500, 'Database connection error: ' . get_called_class());
        }
    }

    /**
     * Вернуть значения полей, входящих в первичный ключ.
     * @param string $table
     * @param array $data
     * @return array
     */
    public function getPkData(string $table, array $data): array
    {
        $pk = $this->getPk($table);

        return array_intersect_key(
            $data,
            array_flip($pk)
        );
    }

    /**
     * Существует ли таблица.
     * @param string $name - название таблицы
     * @return bool
     */
    public function hasTable(string $name): bool
    {
        $tables = $this->getTables();

        if ($tables === null) {
            return false;
        }

        return in_array($name, $tables);
    }

    /**
     * Вернуть названия столбцов, входящих в первичный ключ.
     * @param string $table - название таблицы
     * @return array
     */
    abstract public function getPk(string $table): array;

    /**
     * Список таблиц.
     * @return array
     */
    abstract public function getTables(): array;

    /**
     * Удалить таблицу.
     * @param string $name - название таблицы
     * @return bool
     */
    abstract public function deleteTable(string $name): bool;

    /**
     * Поиск всех записей по значению атрибутов.
     * @param string $table - название таблицы
     * @param array $conditions - условия
     * @return array
     */
    abstract public function findAllByAttributes(string $table, array $conditions): array;

    /**
     * Поиск записи по значению атрибутов.
     * @param string $table - название таблицы
     * @param array $conditions - условия
     * @return array|null
     */
    abstract public function findByAttributes(string $table, array $conditions): ?array;

    /**
     * Добавить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @return array|null
     */
    abstract public function insert(string $table, array $data): ?array;

    /**
     * Изменить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @param array $conditions - условия
     * @return bool
     */
    abstract public function update(string $table, array $data, array $conditions): bool;

    /**
     * Удалить запись.
     * @param string $table - название таблицы
     * @param array $conditions - условия
     * @return bool
     */
    abstract public function delete(string $table, array $conditions): bool;

    /**
     * Подключение к БД.
     * @return bool
     */
    abstract protected function connect(): bool;
}
