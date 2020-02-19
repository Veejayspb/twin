<?php

namespace twin\model\active;

use twin\db\sql\Sql;
use twin\helper\ArrayHelper;
use twin\model\query\Query;
use twin\model\query\SqlQuery;

/**
 * Class ActiveJsonModel
 * @package core\model\active
 *
 * @method static Sql db()
 */
abstract class ActiveSqlModel extends ActiveModel
{
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
        $sql = ArrayHelper::stringExpression($attributes, function ($key, $value) {
            return "`$key`=:$key";
        }, ' AND ');
        return static::find()->where($sql, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if ($this->isNewRecord()) return false;
        if (!$this->beforeDelete()) return false;
        $pk = $this->pk();
        if (empty($pk)) return false;

        $table = static::tableName();
        $pkAttributes = $this->getAttributes($pk);
        $sql = ArrayHelper::stringExpression($pkAttributes, function ($key, $value) {
            return "$key=:$key";
        }, ' AND ');

        $result = static::db()->delete($table, $sql, $pkAttributes);
        if ($result) {
            $this->afterDelete();
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
    protected function insert(): bool
    {
        $table = static::tableName();
        $attributes = $this->getAttributes();
        $result = static::db()->insert($table, $attributes);
        $autoIncrement = static::db()->getAutoIncrement($table);
        if ($result && $autoIncrement !== false && $this->$autoIncrement === null) {
            $this->$autoIncrement = $result;
        }
        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    protected function update(): bool
    {
        $pk = $this->pk();
        if (empty($pk)) return false;

        $table = static::tableName();
        $attributes = $this->getAttributes();
        $pkAttributes = $this->getOriginalAttributes($pk);
        $sql = ArrayHelper::stringExpression($pkAttributes, function ($key, $value) {
            return "$key=:$key";
        }, ', ');
        return static::db()->update($table, $attributes, $sql, $pkAttributes);
    }
}
