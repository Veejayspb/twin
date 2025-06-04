<?php

namespace twin\cache;

use twin\db\sql\Sql;
use twin\Twin;

class CacheSql extends Cache
{
    /**
     * Название компонента с БД SQL.
     * @var string
     */
    public string $db;

    /**
     * Название таблицы БД.
     * @var string
     */
    public string $table = 'cache';

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['db', 'table'];

    /**
     * Создать таблицу для кеша в БД.
     * @return bool
     */
    public function createTable(): bool
    {
        return $this->getConnection()->createTable($this->table, [
            'hash' => 'varchar(255) NOT NULL',
            'key' => 'text NOT NULL',
            'data' => 'text NOT NULL',
            'expires' => 'int(11) NOT NULL',
        ], ['PRIMARY KEY (`hash`)']);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractItem(string $key): ?CacheItem
    {
        $item = new CacheItem;
        $item->key = $key;

        $connection = $this->getConnection();
        $sql = "SELECT * FROM {$this->table} WHERE hash=:hash LIMIT 1";
        $data = $connection->query($sql, [
            ':hash' => $item->getHash(),
        ]);
        $data = array_shift($data);
        if ($data === null) return null;
        return $item->setProperties($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function saveItem(CacheItem $item): bool
    {
        $connection = $this->getConnection();
        $dbItem = $this->extractItem($item->key);

        $data = [
            'hash' => $item->getHash(),
            'key' => $item->key,
            'data' => json_encode($item->data),
            'expires' => $item->expires,
        ];

        if ($dbItem) {
            return $connection->update($this->table, $data, 'hash=:hash', [':hash' => $item->getHash()]);
        } else {
            return null !== $connection->insert($this->table, $data);
        }
    }

    /**
     * Вернуть компонент для подключения к БД.
     * @return Sql
     */
    private function getConnection(): Sql
    {
        return Twin::app()->{$this->db};
    }
}
