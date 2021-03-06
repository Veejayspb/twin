<?php

namespace twin\db;

use twin\common\Component;
use twin\common\Exception;

abstract class Database extends Component
{
    const TYPE_JSON = 'json';
    const TYPE_MYSQL = 'mysql';
    const TYPE_SQLITE = 'sqlite';

    /**
     * Тип БД.
     * @var string
     */
    protected $type;

    /**
     * Название БД.
     * @var string
     */
    protected $dbname;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        if (!$this->connect()) {
            throw new Exception(500, 'Database connection error: ' . get_called_class());
        }
    }

    /**
     * Тип БД.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Название БД.
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbname;
    }

    /**
     * Подключиться к БД.
     * @return bool
     */
    abstract protected function connect(): bool;
}
