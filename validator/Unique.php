<?php

namespace twin\validator;

use twin\model\active\ActiveModel;

/**
 * Class Unique
 * @property ActiveModel $model
 */
class Unique extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ActiveModel $model, array $attributes, array $params = [])
    {
        parent::__construct($model, $attributes, $params);
    }

    /**
     * Проверить запись на уникальность.
     * @param string $attribute
     * @return bool
     */
    public function similar(string $attribute): bool
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
