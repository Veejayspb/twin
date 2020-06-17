<?php

namespace twin\model\relation;

use twin\common\Exception;
use twin\model\active\ActiveModel;

class RelationMany extends Relation
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            throw new Exception(500, 'Relation must be an array of objects');
        }
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     * @return ActiveModel[]
     */
    protected function extractData(ActiveModel $parent)
    {
        $attributes = $this->getAttributes($parent);
        return ($this->model)::findByAttributes($attributes)->all();
    }
}
