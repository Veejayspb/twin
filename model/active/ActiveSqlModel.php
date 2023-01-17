<?php

namespace twin\model\active;

use twin\db\sql\Sql;
use twin\helper\ArrayHelper;
use twin\model\query\Query;
use twin\model\query\SqlQuery;
use twin\model\Transaction;

/**
 * Class ActiveJsonModel
 *
 * @property Transaction $transaction
 * @method static Sql db()
 */
abstract class ActiveSqlModel extends ActiveModel
{
    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($name == 'transaction') {
            return $this->getTransaction();
        }
        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     * @return SqlQuery
     */
    public static function find(): Query
    {
        return new SqlQuery(static::class, static::db());
    }

    /**
     * {@inheritdoc}
     * @return SqlQuery
     */
    public static function findByAttributes(array $attributes): Query
    {
        $sql = ArrayHelper::stringExpression($attributes, function ($key) {
            return "`$key`=:$key";
        }, ' AND ');
        return static::find()->where($sql, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if ($this->transaction->error) return false;
        if ($this->isNewRecord()) return false;
        if (!$this->beforeDelete()) return false;
        $pk = $this->pk();
        if (empty($pk)) return false;

        $pkAttributes = $this->getAttributes($pk);
        $sql = ArrayHelper::stringExpression($pkAttributes, function ($key) {
            return "$key=:$key";
        }, ' AND ');

        $result = static::db()->delete(static::tableName(), $sql, $pkAttributes);
        if ($result) {
            $this->afterDelete();
        }
        if (!$result && $this->transaction->running) {
            $this->transaction->error();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function pk(): array
    {
        $table = static::tableName();
        return static::db()->getPk($table);
    }

    /**
     * {@inheritdoc}
     */
    public function save(bool $validate = true, array $attributes = []): bool
    {
        if ($this->transaction->error) return false;
        $result = parent::save($validate, $attributes);
        if (!$result && $this->transaction->running) {
            $this->transaction->error();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function insert(): bool
    {
        $table = static::tableName();
        $data = $this->getAttributes();
        $result = static::db()->insert($table, $data);
        $autoIncrement = static::db()->getAutoIncrement($table);

        if ($result && $autoIncrement !== false && $this->$autoIncrement === null) {
            $this->$autoIncrement = $result;
        }
        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    protected function update(array $attributes = []): bool
    {
        $pk = $this->pk();
        if (empty($pk)) return false;

        $data = $this->getAttributes($attributes);
        $pkAttributes = $this->getOriginalAttributes($pk);

        $sql = ArrayHelper::stringExpression($pkAttributes, function ($key) {
            return "$key=:$key";
        }, ', ');

        return static::db()->update(static::tableName(), $data, $sql, $pkAttributes);
    }

    /**
     * Вернуть объект для работы с транзакцией.
     * @return Transaction
     */
    private function getTransaction(): Transaction
    {
        return Transaction::get(static::db());
    }
}
