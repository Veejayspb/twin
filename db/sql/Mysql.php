<?php

namespace twin\db\sql;

use twin\common\Exception;
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
    protected $username;

    /**
     * Пароль.
     * @var string
     */
    protected $password;

    /**
     * Путь до директории с файлами БД.
     * @var string
     */
    protected $path;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        if (!isset($properties['dbname'], $properties['username'], $properties['password'])) {
            throw new Exception(500, self::class . ' - required properties not specified: dbname, username, password');
        }
        parent::__construct($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        $sql = 'SHOW TABLES';
        $items = $this->query($sql);
        if ($items === false) return false;
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
        $items = $this->query("SHOW KEYS FROM `$table` WHERE Key_name='PRIMARY'");
        return array_column($items, 'Column_name');
    }

    /**
     * {@inheritdoc}
     */
    protected function connect(): bool
    {
        $this->connection = $dbh = new PDO("mysql:host=localhost;dbname=$this->dbname", $this->username, $this->password);
        return true;
    }
}
