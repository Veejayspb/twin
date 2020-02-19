<?php

namespace core\model\query;

use core\db\Database;
use core\db\sql\Sql;
use core\model\active\ActiveSqlModel;

/**
 * Class SqlQuery
 * @package core\model\query
 *
 * @property Sql $component
 */
class SqlQuery extends Query
{
    /**
     * Select.
     * @var string
     */
    private $select = '*';

    /**
     * From.
     * @var string
     */
    private $from;

    /**
     * Where.
     * @var string
     */
    private $where = '';

    /**
     * Order.
     * @var string
     */
    private $order = '';

    /**
     * Offset.
     * @var int
     */
    private $offset = 0;

    /**
     * Limit.
     * @var int
     */
    private $limit = 0;

    /**
     * Параметры.
     * @var array
     */
    private $params = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(string $modelName, Database $component)
    {
        parent::__construct($modelName, $component);
        $table = $modelName::tableName();
        $this->from($table);
    }

    /**
     * {@inheritdoc}
     * @return ActiveSqlModel|null
     */
    public function one()
    {
        $this->limit(1);
        $sql = $this->getExpression();
        $items = $this->component->query($sql, $this->params);
        if (empty($items)) return null;
        $model = new $this->modelName(false); /* @var ActiveSqlModel $model */
        $model->setAttributes($items[0], false);
        return $model;
    }

    /**
     * {@inheritdoc}
     * @return ActiveSqlModel[]
     */
    public function all(): array
    {
        $sql = $this->getExpression();
        $items = $this->component->query($sql, $this->params);
        if (empty($items)) return [];
        foreach ($items as $item) {
            $model = new $this->modelName(false); /* @var ActiveSqlModel $model */
            $model->setAttributes($item, false);
            $models[] = $model;
        }
        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $this->select('COUNT(*) as `amount`');
        $sql = $this->getExpression();
        $items = $this->component->query($sql, $this->params);
        if (empty($items)) return 0;
        return $items[0]['amount'];
    }

    /**
     * Select.
     * @param string $sql - выражение
     * @return static
     */
    private function select(string $sql): self
    {
        $this->select = $sql;
        return $this;
    }

    /**
     * From.
     * @param string $sql - выражение
     * @return static
     */
    private function from(string $sql): self
    {
        $this->from = $sql;
        return $this;
    }

    /**
     * Where.
     * @param string $sql - выражение
     * @param array $params - параметры
     * @return static
     */
    public function where(string $sql = '', array $params = []): self
    {
        $this->where = $sql;
        $this->params+= $params;
        return $this;
    }

    /**
     * Order.
     * @param string $sql - выражение
     * @return static
     */
    public function order(string $sql = ''): self
    {
        $this->order = $sql;
        return $this;
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
        $this->limit = $value;
        return $this;
    }

    /**
     * Сгенерировать SQL-выражение.
     * @return string
     * @todo: заключить все названия столбцов и таблиц в `кавычки`
     */
    private function getExpression(): string
    {
        $result[] = "SELECT $this->select";
        $result[] = "FROM `$this->from`";
        if (!empty($this->where)) {
            $result[] = "WHERE $this->where";
        }
        if (!empty($this->order)) {
            $result[] = "ORDER BY $this->order";
        }
        if (!empty($this->offset)) {
            $result[] = "OFFSET $this->offset";
        }
        if (!empty($this->limit)) {
            $result[] = "LIMIT $this->limit";
        }
        return implode(' ', $result);
    }
}
