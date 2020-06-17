<?php

namespace twin\model\relation;

use twin\common\Exception;
use twin\model\active\ActiveModel;

abstract class Relation
{
    /**
     * Название модели к которой относится связь.
     * @var string|ActiveModel
     */
    protected $model;

    /**
     * Связь между атрибутами моделей.
     * key - названия атрибутов в родительской модели
     * value - соответствующие названия атрибутов в модели, относящейся к текущей связи
     * @var array
     */
    protected $params;

    /**
     * Кешированные данные, относящиеся к текущей связи.
     * @var ActiveModel|ActiveModel[]|null
     */
    protected $data;

    /**
     * @param string $model - название модели
     * @param array $params - связь между атрибутами моделей
     * @throws Exception
     */
    public function __construct(string $model, array $params)
    {
        if (!is_subclass_of($model, ActiveModel::class)) {
            throw new Exception(500, "$model must extends " . ActiveModel::class);
            // TODO: проверка на принадлежность обеих моделей одному подключению к БД
        }
        $this->model = $model;
        $this->params = $params;
    }

    /**
     * Вернуть готовые данные, относящиеся к текущей связи.
     * @param ActiveModel $parent - родительская модель
     * @return ActiveModel|ActiveModel[]|null
     */
    public function getData(ActiveModel $parent)
    {
        if ($this->data === null) {
            $data = $this->extractData($parent);
            $this->setData($data);
        }
        return $this->data;
    }

    /**
     * Сохранить новый набор данных к кеше.
     * @param mixed $data - набор данных
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Были ли запрошены данные для данной связи.
     * @return bool
     */
    public function dataLoaded(): bool
    {
        return $this->data !== null;
    }

    /**
     * Вернуть массив атрибутов, по которым осуществлять поиск связанной модели.
     * @param ActiveModel $parent - родительская модель
     * @return array
     */
    protected function getAttributes(ActiveModel $parent): array
    {
        $attributes = [];
        foreach ($this->params as $p => $c) {
            $attributes[$c] = $parent->$p;
        }
        return $attributes;
    }

    /**
     * Извлечь связанные данные из БД.
     * @param ActiveModel $parent - родительская модель
     * @return ActiveModel|ActiveModel[]|null
     */
    abstract protected function extractData(ActiveModel $parent);
}
