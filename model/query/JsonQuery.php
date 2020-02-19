<?php

namespace core\model\query;

use core\db\json\Json;
use core\model\active\ActiveJsonModel;

/**
 * Class JsonQuery
 * @package core\model\query
 *
 * @property Json $component
 */
class JsonQuery extends Query
{
    /**
     * Сортировка.
     * @var array
     */
    private $sort = [];

    /**
     * Фильтр.
     * @var callback|null
     */
    private $filter;

    /**
     * Отступ.
     * @var int
     */
    private $offset = 0;

    /**
     * Лимит.
     * @var int|null
     */
    private $limit;

    /**
     * {@inheritdoc}
     * @return ActiveJsonModel|null
     */
    public function one()
    {
        $models = $this->all();
        return count($models) == 0 ? null : $models[0];
    }

    /**
     * {@inheritdoc}
     * @return ActiveJsonModel[]
     */
    public function all(): array
    {
        $items = $this->getData();
        if (empty($items)) return [];
        $models = [];
        foreach ($items as $item) {
            $model = new $this->modelName(false); /* @var ActiveJsonModel $model */
            $model->setAttributes($item, false);
            $models[] = $model;
        }
        return $this->apply($models);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $models = $this->all();
        return count($models);
    }

    /**
     * Добавить фильтр.
     * @param callable|null $callback - коллбэк функция, возвращающая BOOL-значение
     * @return static
     */
    public function filter(callable $callback = null): self
    {
        if ($callback === null || is_callable($callback)) {
            $this->filter = $callback;
        }
        return $this;
    }

    /**
     * Данные таблицы.
     * @return array
     */
    private function getData(): array
    {
        $table = $this->modelName::tableName();
        return $this->component->getData($table);
    }

    /**
     * Применить критерии.
     * @param ActiveJsonModel[] $models
     * @return ActiveJsonModel[]
     */
    private function apply(array $models): array
    {
        $models = $this->applySort($models);
        $models = $this->applyFilter($models);
        $models = $this->applyLimitOffset($models);
        return $models;
    }

    /**
     * Применить сортировку.
     * @param ActiveJsonModel[] $models
     * @return ActiveJsonModel[]
     */
    private function applySort(array $models): array
    {
        usort($models, function (ActiveJsonModel $a, ActiveJsonModel $b) {
            foreach ($this->sort as $name => $value) {

                $aType = gettype($a->$name);
                $bType = gettype($b->$name);

                if ($aType != 'integer' && $aType != 'string') continue;
                if ($bType != 'integer' && $bType != 'string') continue;

                $compare = strcmp($a->$name, $b->$name);
                if ($compare == -1) {
                    return $value == 'ASC' ? -1 : 1;
                } elseif ($compare == 1) {
                    return $value == 'ASC' ? 1 : -1;
                }
            }
            return 0;
        });
        return $models;
    }

    /**
     * Применить фильтр.
     * @param ActiveJsonModel[] $models
     * @return ActiveJsonModel[]
     */
    private function applyFilter(array $models): array
    {
        if ($this->filter !== null) {
            $models = array_filter($models, $this->filter);
        }
        return array_values($models);
    }

    /**
     * Применить отступ и лимит.
     * @param ActiveJsonModel[] $models
     * @return ActiveJsonModel[]
     */
    private function applyLimitOffset(array $models): array
    {
        return array_slice($models, $this->offset, $this->limit);
    }
}
