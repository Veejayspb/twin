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
    public $id;
    public $name;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'test';
    }
}
