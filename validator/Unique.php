<?php

namespace twin\validator;

use twin\common\Exception;
use twin\model\active\ActiveModel;
use twin\model\Model;

/**
 * Class Unique
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
     * Проверить запись на уникальность.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function similar($value, string $label): bool
    {
        $this->message = 'Неуникальное значение';

        if ($this->model->isNewRecord()) {
            return $this->newRecord();
        } else {
            return $this->notNewRecord();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function run()
    {
        $attribute = current($this->attributes);

        if ($attribute !== null) {
            $this->validateAttribute($attribute);
        }
    }

    /**
     * Валидация новой записи.
     * @return bool
     */
    private function newRecord(): bool
    {
        return !$this->hasSimilarRecord();
    }

    /**
     * Валидация существующей записи.
     * @return bool
     */
    private function notNewRecord(): bool
    {
        // Если не указан PK, то невозможно определить оригинальную запись (не валидируем)
        $pk = $this->model->pk();
        if (empty($pk)) {
            return true;
        }

        // Если значения уникальных атрибутов не изменились (не валидируем)
        if (!$this->model->isChangedAttributes($this->attributes)) {
            return true;
        }

        return !$this->hasSimilarRecord();
    }

    /**
     * Имеется ли запись с указанными атрибутами.
     * @return bool
     */
    private function hasSimilarRecord(): bool
    {
        $modelName = get_class($this->model); /* @var ActiveModel $modelName */
        $attributes = $this->model->getAttributes($this->attributes);
        return (bool)$modelName::findByAttributes($attributes)->one();
    }
}
