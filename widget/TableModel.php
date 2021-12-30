<?php

namespace twin\widget;

use twin\model\active\ActiveModel;
use twin\model\query\Query;

class TableModel extends Table
{
    /**
     * Экземпляр запроса.
     * @var Query
     */
    public $query;

    /**
     * {@inheritdoc}
     */
    protected function getTotal(): int
    {
        return $this->query->count();
    }

    /**
     * {@inheritdoc}
     * @return ActiveModel[]
     */
    protected function getItems(): array
    {
        if (!is_subclass_of($this->query, Query::class)) {
            return [];
        }

        $this->sortItems();
        return $this->pagination->apply($this->query)->all();
    }

    /**
     * Сортировка выборки.
     * @return void
     */
    private function sortItems()
    {
        list($sortName, $sortType) = $this->getSortParameter();
        $this->query->order([$sortName => $sortType]);
    }
}
