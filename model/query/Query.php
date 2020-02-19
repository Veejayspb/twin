<?php

namespace twin\model\query;

use twin\db\Database;
use twin\model\active\ActiveModel;

abstract class Query
{
    /**
     * Название модели.
     * @var string
     */
    protected $modelName;

    /**
     * Компонент базы данных.
     * @var Database
     */
    protected $component;

    /**
     * @param string $modelName - название модели
     * @param Database $component - компонент базы данных
     */
    public function __construct(string $modelName, Database $component)
    {
        $this->modelName = $modelName;
        $this->component = $component;
    }

    /**
     * Выборка первой модели.
     * @return ActiveModel|null
     */
    abstract public function one();

    /**
     * Выборка всех моделей.
     * @return ActiveModel[]
     */
    abstract public function all(): array;

    /**
     * Подсчет записей.
     * @return int
     */
    abstract public function count(): int;
}
