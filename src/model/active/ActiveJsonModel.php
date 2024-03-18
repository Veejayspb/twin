<?php

namespace twin\model\active;

use twin\common\Exception;
use twin\db\json\Json;
use twin\helper\ArrayHelper;
use twin\model\query\JsonQuery;
use twin\model\query\Query;

/**
 * Class ActiveJsonModel
 *
 * @method static Json db()
 */
abstract class ActiveJsonModel extends ActiveModel
{
    /**
     * Название автоинкрементного атрибута.
     */
    const AUTO_INCREMENT = 'id';

    /**
     * {@inheritdoc}
     */
    public function __construct(bool $newRecord = true)
    {
        parent::__construct($newRecord);
        $pk = $this->pk();
        if (empty($pk)) {
            throw new Exception(500, 'PK not specified in method pk()');
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function pk(): array
    {
        if ($this->hasAttribute(static::AUTO_INCREMENT)) {
            return [static::AUTO_INCREMENT];
        } else {
            throw new Exception(500, static::class . ' - need to specify PK or create field: ' . static::AUTO_INCREMENT);
        }
    }

    /**
     * {@inheritdoc}
     * @return JsonQuery
     */
    public static function find(): Query
    {
        return new JsonQuery(static::class, static::db());
    }

    /**
     * {@inheritdoc}
     * @return JsonQuery
     */
    public static function findByAttributes(array $attributes): Query
    {
        return static::find()->filter(function (self $model) use ($attributes) {
            foreach ($attributes as $name => $value) {
                if (!$model->hasAttribute($name)) return false;
                if ($value !== $model->$name) return false;
            }
            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        $table = self::tableName();

        $data = static::db()->getData($table);
        $pk = $this->pk();
        $index = ArrayHelper::findByParams($data, $this->getOriginalAttributes($pk));
        if ($index === false) return false;

        unset($data[$index]);
        $data = array_values($data);
        return static::db()->setData($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function insert(): bool
    {
        $this->fillPk();
        $table = self::tableName();

        $data = static::db()->getData($table);
        array_push($data, $this->getAttributes());
        return static::db()->setData($table, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function update(array $attributes = []): bool
    {
        $table = self::tableName();

        $data = static::db()->getData($table);
        $pk = $this->pk();
        $index = ArrayHelper::findByParams($data, $this->getOriginalAttributes($pk));
        if ($index === false) return false;

        $data[$index] = $this->getOriginalAttributes() + $this->getAttributes($attributes);
        return static::db()->setData($table, $data);
    }

    /**
     * Следующий ID.
     * @return int|null - NULL, если отсутствует атрибут с авто-инкрементном
     */
    private function nextId()
    {
        if (!$this->hasAttribute(static::AUTO_INCREMENT)) return null;
        $table = static::tableName();
        $data = static::db()->getData($table);
        if (empty($data)) return 1;
        $ids = array_column($data, static::AUTO_INCREMENT);
        $maxId = max($ids);
        return ++$maxId;
    }

    /**
     * Заполнить ID (если сущ-ет) следующим по порядку значением.
     * @return void
     */
    private function fillPk()
    {
        $pk = $this->pk();

        if ($pk != [self::AUTO_INCREMENT]) return;
        if (!$this->hasAttribute(self::AUTO_INCREMENT)) return;
        if ($this->{self::AUTO_INCREMENT} !== null) return;

        $this->{self::AUTO_INCREMENT} = $this->nextId();
    }
}
