<?php

namespace twin\model\query;

use twin\db\Database;
use twin\db\sql\Sql;
use twin\helper\Html;
use twin\model\active\ActiveSqlModel;

/**
 * Class SqlQuery
 * @package core\model\query
 *
 * @property Sql $component
 */
class SqlQuery extends Query
{
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const INNER_JOIN = 'INNER JOIN';
    const CROSS_JOIN = 'CROSS JOIN';

    /**
     * Select.
     * @var string
     */
    private $select;

    /**
     * From.
     * @var string
     */
    private $from;

    /**
     * Join.
     * @var array
     */
    private $join = [];

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
     * Group.
     * @var string
     */
    private $group = '';

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
        $this->select("`$table`.*");
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
        $model->afterFind();
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
            $model->afterFind();
            $models[] = $model;
        }
        return $models;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $select = $this->select;
        $this->select('COUNT(*) as `amount`');
        $sql = $this->getExpression();
        $this->select($select); // Вернуть прежние поля для выборки
        $items = $this->component->query($sql, $this->params);
        if (empty($items)) return 0;
        if ($this->group) return count($items); // При группировке происходит подсчет по группам (вернет несколько строк)
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
     * Join.
     * @param string $type - тип JOIN
     * @param string $table - название таблицы
     * @param string $on - выражение с условием
     * @return static
     */
    public function join(string $type, string $table, string $on): self
    {
        $this->join[] = "$type `$table` ON $on";
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
     * AND Where.
     * @param string $sql - выражение
     * @param array $params - параметры
     * @return static
     */
    public function andWhere(string $sql, array $params = []): self
    {
        if (empty($this->where)) {
            return $this->where($sql, $params);
        }
        $this->where = "($this->where) AND $sql";
        $this->params+= $params;
        return $this;
    }

    /**
     * OR Where.
     * @param string $sql - выражение
     * @param array $params - параметры
     * @return static
     */
    public function orWhere(string $sql, array $params = []): self
    {
        if (empty($this->where)) {
            return $this->where($sql, $params);
        }
        $this->where = "($this->where) OR $sql";
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
     * Group.
     * @param string $sql - выражение
     * @return static
     */
    public function group(string $sql): self
    {
        $this->group = $sql;
        return $this;
    }

    /**
     * Сгенерировать SQL-выражение.
     * @return string
     */
    private function getExpression(): string
    {
        $result[] = "SELECT $this->select";
        $result[] = "FROM `$this->from`";
        if (!empty($this->join)) {
            $result[] = implode(' ', $this->join);
        }
        if (!empty($this->where)) {
            $result[] = "WHERE $this->where";
        }
        if (!empty($this->group)) {
            $result[] = "GROUP BY $this->group";
        }
        if (!empty($this->order)) {
            $result[] = "ORDER BY $this->order";
        }
        if (!empty($this->limit)) {
            $result[] = "LIMIT $this->limit";
        }
        if (!empty($this->offset)) {
            $result[] = "OFFSET $this->offset";
        }
        return implode(Html::SPACE, $result);
    }
}
