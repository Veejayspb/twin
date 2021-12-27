<?php

namespace twin\model\query;

use twin\db\json\Json;
use twin\model\active\ActiveJsonModel;

/**
 * Class JsonQuery
 * @package core\model\query
 *
 * @property Json $component
 */
class JsonQuery extends Query
{
    /**
     * Фильтр.
     * @var callback|null
     */
    private $filter;

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
            $model->afterFind();
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
            foreach ($this->order as $name => $value) {

                $aType = gettype($a->$name);
                $bType = gettype($b->$name);

                if ($aType != 'integer' && $aType != 'string') continue;
                if ($bType != 'integer' && $bType != 'string') continue;

                $compare = strcmp($a->$name, $b->$name);
                return $compare * ($value === static::ASC ? 1 : -1);
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
