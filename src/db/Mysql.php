<?php

namespace twin\db;

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
    public string $username;

    /**
     * Пароль.
     * @var string
     */
    public string $password;

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['dbName', 'username', 'password'];

    /**
     * {@inheritdoc}
     */
    public function getTables(): array
    {
        $sql = 'SHOW TABLES';
        $items = $this->query($sql);

        if ($items === null) {
            return [];
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

        if ($items === null) {
            return [];
        }

        return array_column($items, 'Column_name');
    }

    /**
     * {@inheridoc}
     */
    public function getAutoIncrement(string $table): ?string
    {
        $items = $this->query("SHOW FULL COLUMNS FROM `$table`");

        if ($items === null) {
            return null;
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
        $this->connection = new PDO("mysql:host=localhost;dbname=$this->dbName", $this->username, $this->password);
        return true;
    }
}
