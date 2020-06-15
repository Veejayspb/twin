<?php

namespace twin\validator;

use twin\common\Exception;
use twin\model\active\ActiveModel;
use twin\model\Model;

/**
 * Class Exists
 * @package twin\validator
 *
 * @property ActiveModel $model
 */
class Exists extends Validator
{
    /**
     * Название модели, в которой осуществлять поиск зависимости.
     * @var string
     */
    public $class;

    /**
     * Название столбца, по которому определять зависимость.
     * @var string
     */
    public $column = 'id';

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
     * Имеется ли родительская запись.
     * @param mixed $value
     * @param string $label
     * @return bool
     */
    public function exists($value, string $label): bool
    {
        $this->message = "Родительская запись не найдена";
        return (bool)($this->class)::findByAttributes([
            $this->column => $value,
        ])->one();
    }
}
