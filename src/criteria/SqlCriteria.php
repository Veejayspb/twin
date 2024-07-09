<?php

namespace twin\criteria;

use twin\common\Exception;
use twin\db\Database;
use twin\db\sql\Sql;
use twin\helper\ArrayHelper;

class SqlCriteria extends Criteria
{
    const DB_CLASS = Sql::class;

    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const INNER_JOIN = 'INNER JOIN';
    const CROSS_JOIN = 'CROSS JOIN';

    /**
     * Поля для выборки.
     * @var array - если пустой массив, то выбираются все поля
     */
    public $select = [];

    /**
     * Присоединение других таблиц.
     * @var array
     */
    public $join = [];

    /**
     * Строка с условиями.
     * @var string
     */
    public $where = '';

    /**
     * Группировка по полям.
     * @var array
     */
    public $group = [];

    /**
     * Параметры.
     * @var array
     */
    public $params = [];

    /**
     * {@inheritdoc}
     * @param Sql $db
     */
    public function query(Database $db): array
    {
        $this->checkDbType($db);
        $sql = $this->generateSql();
        return $db->query($sql, $this->params);
    }

    /**
     * Сгененировать SQL-выражения.
     * @return string
     */
    protected function generateSql(): string
    {
        if ($this->select) {
            $result[] = 'SELECT ' . implode(', ', $this->select);
        } else {
            $result[] = 'SELECT *';
        }

        $result[] = "FROM `$this->from`";

        if ($this->join) {
            $result[] = implode($this->join, ' ');
        }

        if ($this->where) {
            $result[] = 'WHERE ' . $this->where;
        }

        if ($this->group) {
            $result[] = 'GROUP BY ' . implode(', ', $this->group);
        }

        if ($this->order) {
            $result[] = 'ORDER BY ' . ArrayHelper::stringExpression($this->order, function ($k, $v) {
                return $k . ' ' . $v;
            }, ', ');
        }

        if (0 < $this->limit) {
            $result[] = 'LIMIT ' . $this->limit;
        }

        if (0 < $this->offset) {
            $result[] = 'OFFSET ' . $this->offset;
        }

        return implode(' ', $result);
    }
}
