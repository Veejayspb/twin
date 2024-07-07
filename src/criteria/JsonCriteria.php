<?php

namespace twin\criteria;

use twin\db\Database;
use twin\db\json\Json;

class JsonCriteria extends Criteria
{
    const DB_CLASS = Json::class;

    /**
     * Колбэк-функция, которая принимает массив атрибутов и возвращает BOOL.
     * @var callable|null
     */
    public $filter;

    /**
     * {@inheritdoc}
     * @param Json $db
     */
    public function query(Database $db): array
    {
        $this->checkDbType($db);
        $data = $db->getData($this->from);

        // Уникальный ключ каждой записи вынести в кач-ве значения отдельного поля
        foreach ($data as $key => &$row) {
            $row[Json::PK_FIELD] = $key;
        }

        $data = array_values($data);

        if ($this->filter) {
            $data = array_filter($data, $this->filter);
        }

        if ($this->order) {
            uasort($data, function ($a, $b) {
                foreach ($this->order as $field => $order) {
                    if (
                        !in_array($order, [static::ASC, static::DESC], true) ||
                        !isset($a[$field], $b[$field]) ||
                        $a[$field] == $b[$field]
                    ) {
                        continue;
                    }

                    return $a[$field] < $b[$field] ? -1 : 1;
                }

                return 0;
            });
        }

        return array_slice(
            $data,
            $this->offset,
            0 < $this->limit ? $this->limit : null
        );
    }
}
