<?php

namespace twin\model\query;

use twin\db\Database;
use twin\model\active\ActiveModel;

abstract class Query
{
    const ASC = 'ASC';
    const DESC = 'DESC';

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
     * Offset.
     * @var int
     */
    protected $offset = 0;

    /**
     * Limit.
     * @var int|null
     */
    protected $limit;

    /**
     * Order.
     * @var array
     */
    protected $order = [];

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
     * Offset.
     * @param int $value - значение
     * @return static
     */
    public function offset(int $value = 0): self
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * Limit.
     * @param int $value - значение
     * @return static
     */
    public function limit(int $value = 0): self
    {
        $this->limit = $value === 0 ? null : $value;
        return $this;
    }

    /**
     * Order.
     * @param array $value - значение
     * ключ - название поля
     * значение - порядок
     * @return static
     */
    public function order(array $value = []): self
    {
        $this->order = $value;
        return $this;
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
