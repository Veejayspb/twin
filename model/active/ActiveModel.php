<?php

namespace twin\model\active;

use twin\common\Exception;
use twin\db\Database;
use twin\model\Model;
use twin\Twin;
use ReflectionClass;

abstract class ActiveModel extends Model implements ActiveModelInterface
{
    /**
     * Является ли запись новой.
     * @var bool
     */
    protected $_newRecord = true;

    /**
     * Исходное значение атрибутов в БД.
     * @var array|null
     */
    protected $_original;

    /**
     * Название компонента соединения с БД.
     * @var string
     */
    protected static $_component = 'db';

    /**
     * @param bool $newRecord - является ли новой записью
     */
    public function __construct(bool $newRecord = true)
    {
        $this->_newRecord = $newRecord;
    }

    /**
     * Название таблицы.
     * @return string
     */
    public static function tableName(): string
    {
        $result = (new ReflectionClass(static::class))->getShortName();
        $result = preg_replace('/([A-Z])/', '_$1', $result);
        $result = mb_strtolower($result);
        return trim($result, '_');
    }

    /**
     * Является ли запись новой.
     * @return bool
     */
    public function isNewRecord(): bool
    {
        return $this->_newRecord;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes, bool $safeOnly = true)
    {
        parent::setAttributes($attributes, $safeOnly);

        if ($this->_original == null) {
            $this->_original = $this->getAttributes();
            $this->afterFind();
        }
    }

    /**
     * Вернуть оригинальные значения атрибутов.
     * @param array $attributes - названия атрибутов (если указано, то вернет только указанные атрибуты)
     * @return array
     * @see $_original
     */
    public function getOriginalAttributes(array $attributes = []): array
    {
        if (empty($attributes)) return $this->_original;
        return array_filter($this->_original, function ($name) use ($attributes) {
            return in_array($name, $attributes);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Вернуть оригинальное значение атрибута.
     * @param string $name - название атрибута
     * @return string|null
     */
    public function getOriginalAttribute(string $name)
    {
        $attributes = $this->getOriginalAttributes();
        return array_key_exists($name, $attributes) ? $attributes[$name] : null;
    }

    /**
     * Вернуть названия измененных атрибутов.
     * @return array
     */
    public function changedAttributes(): array
    {
        if ($this->isNewRecord()) return [];
        $names = $this->attributeNames();
        $result = [];
        foreach ($names as $name) {
            if ($this->$name === $this->_original[$name]) continue;
            $result[] = $name;
        }
        return $result;
    }

    /**
     * Является ли хоть один из переданных атрибутов измененным.
     * @param array $attributes - названия атрибутов
     * @return bool
     */
    public function isChangedAttributes(array $attributes): bool
    {
        $changed = $this->changedAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute, $changed)) return true;
        }
        return false;
    }

    /**
     * Присвоить значения атрибутов, провалидировать и сохранить.
     * @param array $attributes - значения атрибутов
     * @return bool
     */
    public function load(array $attributes): bool
    {
        $this->setAttributes($attributes);
        return $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function save(bool $validate = true): bool
    {
        if ($validate && !$this->validate()) return false;
        if (!$this->beforeSave()) return false;

        if ($this->isNewRecord()) {
            $result = $this->insert();
        } else {
            $result = $this->update();
        }

        if ($result) {
            $this->afterSave();
            $this->_newRecord = false;
            $this->_original = $this->getAttributes();
        }
        return $result;
    }

    /**
     * Вызов события после поиска записи.
     * @return void
     */
    protected function afterFind() {}

    /**
     * Вызов события до сохранения.
     * @return bool
     */
    protected function beforeSave(): bool
    {
        return true;
    }

    /**
     * Вызов события после сохранения.
     * @return void
     */
    protected function afterSave() {}

    /**
     * Вызов события после удаления.
     * @return bool - если FALSE, то удаление не произойдет
     */
    protected function beforeDelete(): bool
    {
        return true;
    }

    /**
     * Вызов события после удаления.
     * @return void
     */
    protected function afterDelete() {}

    /**
     * Вернуть компонент базы данных.
     * @return Database
     * @throws Exception
     */
    protected static function db(): Database
    {
        $component = Twin::app()->{static::$_component}; /* @var Database $component */
        if (!is_subclass_of($component, Database::class)) {
            throw new Exception(500, 'Component ' . static::$_component . ' must extends ' . Database::class);
        }
        return $component;
    }

    /**
     * Добавить текущую запись в БД.
     * @return bool
     */
    abstract protected function insert(): bool;

    /**
     * Обновить текущую запись в БД.
     * @return bool
     */
    abstract protected function update(): bool;
}
