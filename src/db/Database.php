<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;
use twin\criteria\Criteria;
use twin\migration\Migration;
use twin\model\Model;

abstract class Database extends Component
{
    /**
     * Название БД.
     * @var string
     */
    public $dbname;

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
     * Поиск записей, используя критерии.
     * @param Criteria $criteria
     * @return array
     */
    public function find(Criteria $criteria): array
    {
        return $criteria->query($this);
    }

    /**
     * Поиск моделей, используя критерии.
     * @param string $modelName
     * @param Criteria $criteria
     * @return array
     * @throws Exception
     */
    public function findModels(string $modelName, Criteria $criteria): array
    {
        if (!is_subclass_of($modelName, Model::class)) {
            throw new Exception(500, 'Wrong modelName specified in Database::findModels().');
        }

        $data = $this->find($criteria);
        return $modelName::propagate($data);
    }

    /**
     * Поиск модели, используя критерии.
     * @param string $modelName
     * @param Criteria $criteria
     * @return Model|null
     * @throws Exception
     */
    public function findModel(string $modelName, Criteria $criteria): ?Model
    {
        $criteria->limit = 1;
        $models = $this->findModels($modelName, $criteria);
        return $models ? current($models) : null;
    }

    /**
     * Вернуть названия столбцов, входящих в первичный ключ.
     * @param string $table - название таблицы
     * @return array
     */
    abstract public function getPk(string $table): array;

    /**
     * Создать модель.
     * @param Model $model
     * @return bool
     */
    abstract public function createModel(Model $model): bool;

    /**
     * Обновить модель.
     * @param Model $model
     * @return bool
     */
    abstract public function updateModel(Model $model): bool;

    /**
     * Удалить модель.
     * @param Model $model
     * @return bool
     */
    abstract public function deleteModel(Model $model): bool;

    /**
     * Создать таблицу для миграций.
     * @param string $table - название таблицы с миграциями
     * @return bool
     */
    abstract public function createMigrationTable(string $table): bool;

    /**
     * Применена ли указанная миграция.
     * @param Migration $migration
     * @return bool
     */
    abstract public function isMigrationApplied(Migration $migration): bool;

    /**
     * Добавить миграцию в БД.
     * @param Migration $migration
     * @return bool
     */
    abstract public function addMigration(Migration $migration): bool;

    /**
     * Удалить миграцию из БД.
     * @param Migration $migration
     * @return bool
     */
    abstract public function deleteMigration(Migration $migration): bool;

    /**
     * Подключиться к БД.
     * @return bool
     */
    abstract protected function connect(): bool;
}
