<?php

namespace twin\validator;

use twin\common\Exception;
use twin\model\active\ActiveModel;
use twin\model\Model;

/**
 * Class Unique
 * @package core\validator
 *
 * @property ActiveModel $model
 */
class Unique extends Validator
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(Model $model, $attributes, array $params = [])
    {
        if (!is_subclass_of($model, ActiveModel::class)) {
            throw new Exception(500, get_class($model) . ' must extends ' . ActiveModel::class);
        }
        parent::__construct($model, $attributes, $params);
    }

    /**
     * Проверить существование записи с идентичным PK.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function similar($value, string $label): bool
    {
        $modelName = get_class($this->model); /* @var ActiveModel $modelName */
        $attributes = $this->model->getAttributes($this->attributes);
        $count = $modelName::findByAttributes($attributes)->count();

        // Для новой записи не должно сущ-вать дублей
        if ($this->model->isNewRecord()) {
            return $count == 0;
        }
        $pk = $this->model->pk();
        // Если не указан PK, то невозможно определить оригинальную запись (не валидируем)
        if (empty($pk)) {
            return true;
        }
        // Если PK у сущ-щей записи сменился, то для нее также не должно сущ-вать дублей
        if ($this->model->isChangedAttributes($pk)) {
            return $count == 0;
        }
        // Если PK у сущ-щей записи остался прежним, то в БД присутствует одна запись - текущая
        return $count <= 1;
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        $attributes = $this->attributes;
        $attribute = array_pop($attributes);
        if ($attribute !== null) {
            $this->validateAttribute($attribute);
        }
    }
}
