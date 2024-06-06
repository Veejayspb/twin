<?php

namespace twin\criteria;

use twin\common\Exception;
use twin\db\Database;

abstract class Criteria
{
    const DB_CLASS = Database::class;

    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * Выбрать из таблицы.
     * @var string
     */
    public $from;

    /**
     * Отступ.
     * @var int - если 0, то отступ отсутствует
     */
    public $offset = 0;

    /**
     * Лимит.
     * @var int - если 0, то лимит отсутствует
     */
    public $limit = 0;

    /**
     * Порядок сортировки.
     * Ключ - название атрибута.
     * Значение - направление сортировки.
     * @var array
     */
    public $order = [];

    /**
     * Вернуть массив данных из указанной БД, используя текущие критерии.
     * @param Database $db
     * @return array
     */
    abstract public function query(Database $db): array;

    /**
     * Проверка типа БД.
     * Должен соответствовать классу критерии.
     * @param Database $db
     * @throws Exception
     */
    protected function checkDbType(Database $db): void
    {
        if (!is_a($db, static::DB_CLASS)) {
            throw new Exception(500, 'You can use ' . static::class . ' criteria only for ' . static::DB_CLASS);
        }
    }
}
