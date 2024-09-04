<?php

namespace twin\validator;

use twin\db\Database;

class Exists extends Validator
{
    /**
     * Объект для работы с БД.
     * @var Database
     */
    public $db;

    /**
     * Название родительской таблицы.
     * @var string
     */
    public $table;

    /**
     * Условия для поиска по родительской таблице.
     * @var array
     */
    public $conditions = [];

    /**
     * Имеется ли родительская запись.
     * @param string $attribute
     * @return bool
     */
    public function exists(string $attribute): bool
    {
        $this->setMessage('Родительская запись не найдена');
        $row = $this->db->findByAttributes($this->table, $this->conditions);

        return $row !== null;
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        // Применять валидатор только к первому атрибуту
        $attribute = current($this->attributes);

        if (!$attribute) {
            return;
        }

        $this->validateAttribute($attribute);
    }
}
