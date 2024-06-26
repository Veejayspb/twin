<?php

namespace twin\db\sql;

use PDO;

class Mysql extends Sql
{
    const ENGINE_INNODB = 'InnoDB';
    const ENGINE_MYISAM = 'MyISAM';
    const ENGINE_MEMORY = 'Memory';
    const ENGINE_CSV = 'CSV';
    const ENGINE_MERGE = 'Merge';
    const ENGINE_ARCHIVE = 'Archive';
    const ENGINE_FEDERATED = 'Federated';
    const ENGINE_BLACKHOLE = 'Blackhole';
    const ENGINE_EXAMPLE = 'Example';

    /**
     * Имя пользователя.
     * @var string
     */
    public $username;

    /**
     * Пароль.
     * @var string
     */
    public $password;

    /**
     * {@inheritdoc}
     */
    protected $_requiredProperties = ['dbname', 'username', 'password'];

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        $sql = 'SHOW TABLES';
        $items = $this->query($sql);

        if ($items === false) {
            return false;
        }

        $result = [];

        foreach ($items as $item) {
            $result[] = array_pop($item);
        }

        return $result;
    }

    /**
     * {@inheridoc}
     */
    public function getPk(string $table): array
    {
        $sql = "SHOW KEYS FROM `$table` WHERE Key_name='PRIMARY'";
        $items = $this->query($sql);

        if ($items === false) {
            return [];
        }

        return array_column($items, 'Column_name');
    }

    /**
     * {@inheridoc}
     */
    public function getAutoIncrement(string $table)
    {
        $items = $this->query("SHOW FULL COLUMNS FROM `$table`");

        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if (array_key_exists('Extra', $item) && $item['Extra'] == 'auto_increment') {
                return $item['Field'];
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function transactionBegin(): bool
    {
        return $this->execute('START TRANSACTION');
    }

    /**
     * {@inheritdoc}
     */
    protected function connect(): bool
    {
        $this->connection = new PDO("mysql:host=localhost;dbname=$this->dbname", $this->username, $this->password);
        return true;
    }
}
