<?php

namespace twin\validator;

use twin\db\Database;
use twin\helper\ArrayHelper;

class Unique extends Validator
{
    /**
     * Объект для работы с БД.
     * @var Database
     */
    public $db;

    /**
     * Поиск идентичной записи.
     * @param string $attribute
     * @return bool
     */
    public function similar(string $attribute): bool
    {
        $label = $this->model->getLabel($attribute);
        $this->setMessage("$label не является уникальным");

        $table = $this->model::tableName();
        $pk = $this->db->getPk($table);
        $pkAttributes = $this->model->getAttributes($pk);
        $uniqueAttributes = $this->model->getAttributes($this->attributes);
        $row = $this->db->findByAttributes($table, $uniqueAttributes);

        if ($row === null) {
            return true;
        }

        return ArrayHelper::hasElements($row, $pkAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        // Применять валидатор только к первому атрибуту из списка уникальных
        $attribute = current($this->attributes);

        if (!$attribute) {
            return;
        }

        $this->validateAttribute($attribute);
    }
}
