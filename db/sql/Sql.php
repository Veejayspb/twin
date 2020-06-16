<?php

namespace twin\db\sql;

use twin\db\Database;
use twin\helper\ArrayHelper;
use PDO;

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
    public $connection;

    /**
     * {@inheritdoc}
     */
    protected $type = self::TYPE_SQL;

    /**
     * Индикатор запуска транзакции.
     * @var bool
     */
    protected $transaction = false;

    /**
     * Лог SQL-запросов в рамках текущего HTTP-запроса.
     * Предотвращает одинаковые повторные обращения к БД.
     * Ключ - SQL-выражение.
     * Значение - результат.
     * @var array
     */
    protected $queryLog = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->connect();
    }

    /**
     * Осуществить запрос в БД, используя лог запросов, и вернуть ответ.
     * @param string $sql - SQL-выражение
     * @param array $params - параметры
     * @return array|bool - FALSE в случае ошибки
     * @todo: добавить 3й параметр cache, который будет использовать кеш запросов
     */
    public function query(string $sql, array $params = [])
    {
        if (empty($params)) {
            if (array_key_exists($sql, $this->queryLog)) {
                $data = $this->queryLog[$sql];
            } else {
                $data = $this->directQuery($sql);
                if ($data !== false) {
                    $this->queryLog[$sql] = $data;
                }
            }
        } else {
            $data = $this->directQuery($sql, $params);
        }
        return $data;
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
        if ($statement === false) return false;
        return $statement->execute($params);
    }

    /**
     * Добавить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @return int|bool - ID новой записи, либо FALSE в случае ошибки
     */
    public function insert(string $table, array $data)
    {
        $keys = array_keys($data);

        $placeholders = array_map(function ($key) {
            return ":$key";
        }, $keys);

        $keysStr = implode('`, `', $keys);
        $phStr = implode(', ', $placeholders);
        $sql = "INSERT INTO `$table` (`$keysStr`) VALUES ($phStr)";
        $result = $this->query($sql, array_combine($placeholders, $data));

        if ($result === false) return false;
        return $this->connection->lastInsertId();
    }

    /**
     * Обновить запись.
     * @param string $table - название таблицы
     * @param array $data - данные
     * @param string $where - SQL-выражение с условиями (после WHERE)
     * @param array $params - параметры
     * @return bool
     */
    public function update(string $table, array $data, string $where, array $params = []): bool
    {
        $set = ArrayHelper::stringExpression($data, function ($key, $value) {
            return "`$key`=:" . self::PREFIX . $key;
        }, ', ');

        foreach ($data as $key => $value) {
            $params[self::PREFIX . $key] = $value;
        }

        $sql = "UPDATE `$table` SET $set WHERE $where";
        return $this->execute($sql, $params);
    }

    /**
     * Удалить запись.
     * @param string $table - название таблицы
     * @param string $where - SQL-выражение с условиями (после WHERE)
     * @param array $params - параметры
     * @return bool
     */
    public function delete(string $table, string $where, array $params = []): bool
    {
        $sql = "DELETE FROM `$table` WHERE $where";
        $result = $this->query($sql, $params);
        return $result !== false;
    }

    /**
     * Создать таблицу.
     * @param string $name - название таблицы
     * @param array $columns - список столбцов (ключ - название, значение - параметры)
     * @param array $pk - названия полей, входящих PK
     * @return bool
     */
    public function createTable(string $name, array $columns, array $pk = []): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `$name` (";
        $sql.= ArrayHelper::stringExpression($columns, function ($column, $expression) {
            return "`$column` $expression";
        }, ', ');
        if (!empty($pk)) {
            $sql .= ', PRIMARY KEY (';
            $sql.= ArrayHelper::stringExpression($pk, function ($i, $name) {
                return "`$name`";
            }, ', ');
            $sql.= ')';
        }
        $sql.= ");";
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
     * Вернуть название автоинкрементного поля.
     * @param string $table - название таблицы
     * @return string|bool - FALSE в случае отсутствия автоинкремента, либо ошибки
     */
    public function getAutoIncrement(string $table)
    {
        $items = $this->query("SHOW FULL COLUMNS FROM `$table`");
        if ($items === false) return false;
        foreach ($items as $item) {
            if (array_key_exists('Extra', $item) && $item['Extra'] == 'auto_increment') {
                return $item['Field'];
            }
        }
        return false;
    }

    /**
     * Применить транзакцию.
     * @return bool
     */
    public function transactionCommit(): bool
    {
        if (!$this->transaction) {
            $this->transaction = false;
            return $this->execute('COMMIT');
        }
        return false;
    }

    /**
     * Откатить транзакцию.
     * @return bool
     */
    public function transactionRollback(): bool
    {
        if (!$this->transaction) {
            $this->transaction = false;
            return $this->execute('ROLLBACK');
        }
        return false;
    }

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

    /**
     * Вернуть названия столбцов, входящих в первичный ключ.
     * @param string $table - название таблицы
     * @return array
     */
    abstract public function getPk(string $table): array;

    /**
     * Осуществить запрос в БД и вернуть ответ.
     * @param string $sql - SQL-выражение
     * @param array $params - параметры
     * @return array|bool - FALSE в случае ошибки
     */
    protected function directQuery(string $sql, array $params = [])
    {
        $statement = $this->connection->prepare($sql);
        if ($statement === false) return false;
        $result = $statement->execute($params);
        if ($result === false) return false;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
