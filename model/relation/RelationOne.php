<?php

namespace twin\model\relation;

use twin\common\Exception;
use twin\model\active\ActiveModel;

class RelationOne extends Relation
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function setData($data)
    {
        if (!is_a($this->model, $this->model, true)) {
            throw new Exception(500, "Relation must belongs to class $this->model");
        }
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     * @return ActiveModel|null
     */
    protected function extractData(ActiveModel $parent)
    {
        $attributes = $this->getAttributes($parent);
        return ($this->model)::findByAttributes($attributes)->one();
    }
}
