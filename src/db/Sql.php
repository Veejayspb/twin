<?php

namespace twin\db;

use PDO;
use twin\helper\ArrayHelper;

abstract class Sql extends Database
{
    /**
     * Префикс плейсхолдеров для data-параметров в запросах.
     */
    const PREFIX = 'd_';

    /**
     * Идентификатор соединения.
     * @var PDO
     */
    protected PDO $connection;

    /**
     * Осуществить запрос в БД и вернуть ответ.
     * @param string $sql - SQL-выражение
     * @param array $params - параметры
     * @return array|null
     */
    public function query(string $sql, array $params = []): ?array
    {
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            return null;
        }

        $result = $statement->execute($params);

        if (!$result) {
            return null;
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
        $sql.= ')';

        return $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTable(string $name): bool
    {
        return $this->execute("DROP TABLE IF EXISTS `$name`");
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
    public function findAllByAttributes(string $table, array $conditions): array
    {
        $params = $this->generatePlaceholdersList($conditions);
        $sql = "SELECT * FROM `$table`";

        if ($conditions) {
            $sql.= " WHERE ";
            $sql.= $this->generateExpression($conditions);
        }

        $rows = $this->query($sql, $params);

        return $rows === null ? [] : $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function findByAttributes(string $table, array $conditions): ?array
    {
        $params = $this->generatePlaceholdersList($conditions);
        $sql = "SELECT * FROM `$table`";

        if ($conditions) {
            $sql.= " WHERE ";
            $sql.= $this->generateExpression($conditions);
        }

        $sql.= " LIMIT 1";

        $rows = $this->query($sql, $params);

        if ($rows === null) {
            return null;
        }

        return current($rows);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table, array $data): ?array
    {
        if (empty($data)) {
            return null;
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
            return null;
        }

        // Заполнить поле ID значением
        $autoIncrementField = $this->getAutoIncrement($table);
        $lastInsertId = $this->lastInsertId();

        if ($autoIncrementField && $lastInsertId) {
            $data[$autoIncrementField] = $lastInsertId;
        }

        return $this->getPkData($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table, array $data, array $conditions): bool
    {
        if (empty($data)) {
            return true;
        }

        $set = $this->generateExpression($data, self::PREFIX, ', ');
        $params = $this->generatePlaceholdersList($data, self::PREFIX);
        $sql = "UPDATE `$table` SET $set";

        if (!empty($conditions)) {
            $where = $this->generateExpression($conditions);
            $sql.= " WHERE $where";
            $params+= $this->generatePlaceholdersList($conditions);
        }

        return $this->execute($sql, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $table, array $conditions): bool
    {
        $sql = "DELETE FROM `$table`";
        $params = [];

        if (!empty($conditions)) {
            $where = $this->generateExpression($conditions);
            $sql.= " WHERE $where";
            $params = $this->generatePlaceholdersList($conditions);
        }

        return $this->execute($sql, $params);
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
     * Сгенерировать SQL-выражение вида:
     * a=:a AND b=:b
     * a=:a, b=:b
     * a=:prefix_a, b=:prefix_b
     * @param array $conditions - значения атрибутов
     * @param string $prefix - префикс плейсхолдеров
     * @param string $separator - разделитель
     * @return string
     */
    protected function generateExpression(array $conditions, string $prefix = '', string $separator = ' AND '): string
    {
        return ArrayHelper::stringExpression($conditions, function ($key, $value) use ($prefix) {
            return "`$key`=:$prefix$key";
        }, $separator);
    }

    /**
     * Сгенерировать массив плейсхолдеров вида:
     * ['a' => ':a', 'b' => ':b']
     * ['a' => ':prefix_a', 'b' => ':prefix_b']
     * @param array $conditions - значения атрибутов
     * @param string $prefix - префикс плейсхолдеров
     * @return array
     */
    protected function generatePlaceholdersList(array $conditions, string $prefix = ''): array
    {
        $params = [];

        foreach ($conditions as $key => $value) {
            $params[":$prefix$key"] = $value;
        }

        return $params;
    }

    /**
     * ID последней добавленной записи.
     * @return string
     */
    protected function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Вернуть название автоинкрементного поля.
     * @param string $table - название таблицы
     * @return string|null
     */
    abstract public function getAutoIncrement(string $table): ?string;

    /**
     * Начать транзакцию.
     * @return bool
     */
    abstract public function transactionBegin(): bool;
}
