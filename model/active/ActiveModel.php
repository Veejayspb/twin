<?php

namespace twin\model\active;

use twin\common\Exception;
use twin\db\Database;
use twin\event\Event;
use twin\event\EventActiveModel;
use twin\model\Model;
use twin\model\relation\Relation;
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
     * @var array
     */
    protected $_original = [];

    /**
     * Связи с другими моделями.
     * @var Relation[]
     */
    protected $_relations = [];

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
        parent::__construct();
        
        $this->_newRecord = $newRecord;
        $this->_relations = $this->relations();
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $relation = $this->getRelation($name);

        if ($relation) {
            return $relation->getData($this);
        }

        return parent::__get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $relation = $this->getRelation($name);

        if ($relation) {
            $relation->setData($value);
        }

        parent::__set($name, $value);
    }

    /**
     * Название таблицы.
     * @return string
     */
    public static function tableName(): string
    {
        $reflection = new ReflectionClass(static::class);

        $result = $reflection->getShortName();
        $result = preg_replace('/([A-Z])/', '_$1', $result);
        $result = mb_strtolower($result);

        return trim($result, '_');
    }

    /**
     * Вернуть компонент базы данных.
     * @return Database
     * @throws Exception
     */
    public static function db(): Database
    {
        $component = Twin::app()->{static::$_component}; /* @var Database $component */

        if (!is_subclass_of($component, Database::class)) {
            throw new Exception(500, 'Component ' . static::$_component . ' must extends ' . Database::class);
        }

        return $component;
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
    public function setAttributes(array $attributes, bool $safeOnly = true): Model
    {
        parent::setAttributes($attributes, $safeOnly);

        if (!$this->_original && !$this->isNewRecord()) {
            $this->_original = $this->getAttributes();
        }

        return $this;
    }

    /**
     * Вернуть оригинальное значение атрибута.
     * @param string $attribute - название атрибута
     * @return mixed|null
     */
    public function getOriginalAttribute(string $attribute)
    {
        return $this->_original[$attribute] ?? null;
    }

    /**
     * Вернуть оригинальные значения атрибутов.
     * @param array $attributes - названия атрибутов (если указано, то вернет только указанные атрибуты)
     * @return array
     * @see $_original
     */
    public function getOriginalAttributes(array $attributes = []): array
    {
        if (empty($attributes)) {
            return $this->_original;
        }

        return array_filter($this->_original, function ($name) use ($attributes) {
            return in_array($name, $attributes);
        }, ARRAY_FILTER_USE_KEY);
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
            $original = $this->getOriginalAttribute($name);
            if ($this->$name == $original) continue;
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
    public function save(bool $validate = true, array $attributes = []): bool
    {
        if ($validate && !$this->validate($attributes)) {
            return false;
        }

        if (!$this->beforeSave()) {
            return false;
        }

        if ($this->isNewRecord()) {
            $result = $this->insert();
        } else {
            $result = $this->update($attributes);
        }

        if ($result) {
            $this->afterSave();
            $this->_original = $this->getOriginalAttributes() + $this->getAttributes($attributes);
        }

        return $result;
    }

    /**
     * Обновить атрибуты модели.
     * @param bool $force - если FALSE, то атрибуты обновятся через кэш original, если TRUE - через БД
     * @return static
     */
    public function refresh(bool $force = false): self
    {
        if ($force) {
            $pk = $this->pk();
            $pkAttributes = $this->getOriginalAttributes($pk);
            $model = static::findByAttributes($pkAttributes)->one();
            $attributes = $model ? $model->getAttributes() : [];
        } else {
            $attributes = $this->getOriginalAttributes();
        }

        return $this->setAttributes($attributes, false);
    }

    /**
     * Вернуть объект со связью.
     * @param string $name - название связи
     * @return Relation|null
     */
    public function getRelation(string $name)
    {
        return $this->_relations[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     * @return EventActiveModel
     */
    public function event(): Event
    {
        return $this->_event = $this->_event ?: new EventActiveModel($this);
    }

    /**
     * Связи с другими моделями.
     * key - название связи
     * value - объект связи
     * @return Relation[]
     */
    protected function relations(): array
    {
        return [];
    }

    /**
     * Вызов события после поиска записи.
     * @return void
     */
    public function afterFind() {}

    /**
     * Вызов события до сохранения.
     * @return bool
     */
    protected function beforeSave(): bool
    {
        $this->event()->beforeSave();
        return true;
    }

    /**
     * Вызов события после сохранения.
     * @return void
     */
    protected function afterSave()
    {
        $this->_newRecord = false;
        $this->event()->afterSave();
    }

    /**
     * Вызов события после удаления.
     * @return bool - если FALSE, то удаление не произойдет
     */
    protected function beforeDelete(): bool
    {
        $this->event()->beforeDelete();
        return true;
    }

    /**
     * Вызов события после удаления.
     * @return void
     */
    protected function afterDelete()
    {
        $this->event()->afterDelete();
    }

    /**
     * Добавить текущую запись в БД.
     * @return bool
     */
    abstract protected function insert(): bool;

    /**
     * Обновить текущую запись в БД.
     * @param array $attributes - названия атрибутов, которые будут обновлены
     * @return bool
     */
    abstract protected function update(array $attributes = []): bool;
}
