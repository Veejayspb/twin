<?php

namespace twin\db\sql;

use PDO;
use twin\criteria\SqlCriteria;
use twin\db\Database;
use twin\helper\ArrayHelper;
use twin\migration\Migration;

abstract class Sql extends Database
{
    /**
     * Префикс placeholder'ов для data-параметров в запросах.
     */
    const PREFIX = 'd_';

    /**
     * Идентификатор соединения.
     * @var PDO
     */
    protected $connection;

    /**
     * Осуществить запрос в БД и вернуть ответ.
     * @param string $sql - SQL-выражение
     * @param array $params - параметры
     * @return array|bool - FALSE в случае ошибки
     */
    public function query(string $sql, array $params = [])
    {
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            return false;
        }

        $result = $statement->execute($params);

        if (!$result) {
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Осуществить запрос в БД.
     * @param string $sql - SQL-выражение
     * @param array $params - параметры
     * @return bool
     */
    public function execute(string $sql, array $params = []): bool
    {
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            return false;
        }

        return $statement->execute($params);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByAttributes(string $table, array $attributes): array
    {
        $criteria = new SqlCriteria;
        $criteria->from = $table;

        $criteria->where = ArrayHelper::stringExpression($attributes, function ($key, $value) {
            return "`$key`=:$key";
        }, ' AND ');

        $criteria->params = ArrayHelper::column($attributes, function ($key, $value) {
            return ":$key";
        }, function ($key, $value) {
            return $value;
        });

        return $this->findAll($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findByAttributes(string $table, array $attributes): ?array
    {
        $criteria = new SqlCriteria;
        $criteria->from = $table;
        $criteria->limit = 1;

        $criteria->where = ArrayHelper::stringExpression($attributes, function ($key, $value) {
            return "`$key`=:$key";
        }, ' AND ');

        $criteria->params = ArrayHelper::column($attributes, function ($key, $value) {
            return ":$key";
        }, function ($key, $value) {
            return $value;
        });

        return $this->find($criteria);
    }

    /**
     * Добавить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @return string|bool - ID новой записи, либо FALSE в случае ошибки
     */
    public function insert(string $table, array $data)
    {
        if (empty($data)) {
            return false;
        }

        $keys = array_keys($data);

        $placeholders = array_map(function ($key) {
            return ":$key";
        }, $keys);

        $keysStr = implode('`, `', $keys);
        $phStr = implode(', ', $placeholders);
        $sql = "INSERT INTO `$table` (`$keysStr`) VALUES ($phStr)";
        $result = $this->execute($sql, array_combine($placeholders, $data));

        if (!$result) {
            return false;
        }

        return $this->connection->lastInsertId();
    }

    /**
     * Обновить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @param string|null $where - SQL-выражение с условиями (после WHERE)
     * @param array $params - параметры
     * @return bool
     */
    public function update(string $table, array $data, ?string $where = null, array $params = []): bool
    {
        if (empty($data)) {
            return true;
        }

        $set = ArrayHelper::stringExpression($data, function ($key, $value) {
            return "`$key`=:" . self::PREFIX . $key;
        }, ', ');

        foreach ($data as $key => $value) {
            $params[':' . self::PREFIX . $key] = $value;
        }

        $sql = "UPDATE `$table` SET $set";

        if (!empty($where)) {
            $sql.= " WHERE $where";
        }

        return $this->execute($sql, $params);
    }

    /**
     * Удалить запись.
     * @param string $table - название таблицы
     * @param string|null $where - SQL-выражение с условиями (после WHERE)
     * @param array $params - параметры
     * @return bool
     */
    public function delete(string $table, ?string $where = null, array $params = []): bool
    {
        $sql = "DELETE FROM `$table`";

        if (!empty($where)) {
            $sql.= " WHERE $where";
        }

        return $this->execute($sql, $params);
    }

    /**
     * Создать таблицу.
     * @param string $name - название таблицы
     * @param array $columns - список столбцов (ключ - название, значение - параметры)
     * @param array $keys - дополнительные строки с ключами:
     * PRIMARY KEY (`id`)
     * FOREIGN KEY (`record_id`) REFERENCES `table` (`id`)
     * @return bool
     */
    public function createTable(string $name, array $columns, array $keys = []): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `$name` (";
        $sql.= ArrayHelper::stringExpression($columns, function ($column, $expression) {
            return "`$column` $expression";
        }, ', ');

        $sql.= empty($keys) ? '' : ', ';
        $sql.= implode(', ', $keys);
        $sql.= ")";

        return $this->execute($sql);
    }

    /**
     * Удалить таблицу.
     * @param string $name - название таблицы
     * @return bool
     */
    public function deleteTable(string $name): bool
    {
        return $this->execute("DROP TABLE IF EXISTS `$name`");
    }

    /**
     * Существует ли таблица.
     * @param string $name - название таблицы
     * @return bool
     */
    public function hasTable(string $name): bool
    {
        $tables = $this->getTables();

        if ($tables === false) {
            return false;
        }

        return in_array($name, $tables);
    }

    /**
     * Применить транзакцию.
     * @return bool
     */
    public function transactionCommit(): bool
    {
        return $this->execute('COMMIT');
    }

    /**
     * Откатить транзакцию.
     * @return bool
     */
    public function transactionRollback(): bool
    {
        return $this->execute('ROLLBACK');
    }

    /**
     * {@inheritdoc}
     */
    public function createMigrationTable(string $table): bool
    {
        return $this->createTable($table, [
            'hash' => 'VARCHAR(32) NOT NULL',
            'name' => 'TEXT NOT NULL',
            'timestamp' => 'INT NOT NULL',
        ], [
            'PRIMARY KEY (`hash`)',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function isMigrationApplied(Migration $migration): bool
    {
        $table = $migration->getManager()->table;

        if (!$this->hasTable($table) && !$this->createMigrationTable($table)) {
            return false;
        }

        $sql = "SELECT * FROM `$table` WHERE hash = :hash LIMIT 1";
        $items = $this->query($sql, ['hash' => $migration->getHash()]);

        return !empty($items);
    }

    /**
     * {@inheritdoc}
     */
    public function addMigration(Migration $migration): bool
    {
        $result = $this->insert($migration->getManager()->table, [
            'hash' => $migration->getHash(),
            'name' => $migration->getClass(),
            'timestamp' => time(),
        ]);

        return $result !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMigration(Migration $migration): bool
    {
        return $this->delete(
            $migration->getManager()->table,
            'hash = :hash',
            ['hash' => $migration->getHash()]
        );
    }

    /**
     * Вернуть название автоинкрементного поля.
     * @param string $table - название таблицы
     * @return string|bool - FALSE в случае отсутствия автоинкремента, либо ошибки
     */
    abstract public function getAutoIncrement(string $table);

    /**
     * Начать транзакцию.
     * @return bool
     */
    abstract public function transactionBegin(): bool;

    /**
     * Список таблиц.
     * @return array|bool - FALSE в случае ошибки
     */
    abstract public function getTables();
}
