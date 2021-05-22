<?php

namespace twin\migration;

use twin\common\Exception;
use twin\db\sql\Sql;
use twin\Twin;

class MigrationManagerSql extends MigrationManager
{
    /**
     * Название компонента для работы с SQL-БД.
     * @var string
     */
    public $component;

    /**
     * Название таблицы в БД.
     * @var string
     */
    public $table = 'migration';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (!is_subclass_of($this->getDb(), Sql::class)) {
            throw new Exception(500, "\"{$this->component}\" component must be of class " . Sql::class);
        }
        $this->createTable();
    }

    /**
     * {@inheritdoc}
     */
    protected function setTimestamp(int $timestamp): bool
    {
        $db = $this->getDb();
        if ($timestamp == 0) {
            return $db->deleteTable($this->table);
        }

        $sql = $this->getSql();
        $rows = $db->query($sql);
        $data = [
            'key' => 'timestamp',
            'value' => $timestamp,
        ];

        if (empty($rows)) {
            return $db->insert($this->table, $data);
        } else {
            return $db->update($this->table, $data, "`key`='timestamp'");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTimestamp(): int
    {
        $sql = $this->getSql();
        $data = $this->getDb()->query($sql);
        return (int)($data[0]['value'] ?? 0);
    }

    /**
     * SQL-выражение для выборки единственной записи.
     * @return string
     */
    private function getSql(): string
    {
        return "SELECT * FROM `{$this->table}` WHERE `key`='timestamp' LIMIT 1";
    }

    /**
     * Создать таблицу для сохранения данных.
     * @return bool
     */
    private function createTable(): bool
    {
        return $this->getDb()->createTable($this->table, [
            'key' => 'VARCHAR(255) NOT NULL',
            'value' => 'INT NOT NULL',
        ], [
            'PRIMARY KEY (`key`)',
        ]);
    }

    /**
     * Компонент для работы с SQL-БД.
     * @return Sql
     */
    private function getDb(): Sql
    {
        return Twin::app()->{$this->component};
    }
}
