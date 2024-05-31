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
