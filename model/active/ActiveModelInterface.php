<?php

namespace twin\model\active;

use twin\model\query\Query;

interface ActiveModelInterface
{
    /**
     * Названия атрибутов, формирующих первичный ключ.
     * @return array
     */
    public function pk(): array;

    /**
     * Поиск.
     * @return Query
     */
    public static function find(): Query;

    /**
     * Поиск по значениям атрибутов.
     * @param array $attributes - атрибуты
     * @return Query
     */
    public static function findByAttributes(array $attributes): Query;

    /**
     * Удаление записи.
     * @return bool
     */
    public function delete(): bool;

    /**
     * Сохранение записи.
     * @param bool $validate - валидировать
     * @return bool
     */
    public function save(bool $validate = true): bool;
}
