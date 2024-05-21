<?php

namespace test\helper;

use twin\model\Model;

/**
 * Class TestModel
 * @property int $id
 * @property string $name
 */
final class TestModel extends Model
{
    /**
     * @var array
     */
    public $_pk = ['id'];

    /**
     * {@inheritdoc}
     */
    public function pkAttributes(): array
    {
        return $this->_pk;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'test';
    }

    /**
     * {@inheritdoc}
     */
    protected function attributeNames(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
