<?php

namespace twin\widget;

class TableArray extends Table
{
    /**
     * Массив данных для вывода.
     * @var array
     */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    protected function getTotal(): int
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    protected function getItems(): array
    {
        if (!is_array($this->items)) {
            return [];
        }

        $items = $this->sortItems($this->items);
        $offset = $this->getPagination()->offset;
        $limit = $this->getPagination()->limit;

        return array_slice($items, $offset, $limit);
    }

    /**
     * Сортировка элементов.
     * @param array $items
     * @return array
     */
    private function sortItems(array $items): array
    {
        list($sortName, $sortType) = $this->getSortParameter();
        if ($sortName === null) {
            return $items;
        }

        $column = $this->getColumnByName($sortName);
        if ($column === false) {
            return $items;
        }

        usort($items, function ($a, $b) use ($column, $sortType) {
            $result = strcmp(
                call_user_func($column->value, $a),
                call_user_func($column->value, $b)
            );
            return $result * ($sortType === static::ASC ? 1 : -1);
        });

        return $items;
    }
}
