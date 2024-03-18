<?php

namespace twin\behavior\collection;

use twin\behavior\BehaviorActiveModel;
use twin\event\EventActiveModel;

class BehaviorTimestamp extends BehaviorActiveModel
{
    /**
     * Название поля с датой создания.
     * @var string
     */
    public $create = 'date_create';

    /**
     * Название поля с датой изменения.
     * @var string
     */
    public $update = 'date_update';

    /**
     * {@inheritdoc}
     */
    public function touch(string $event)
    {
        if ($event == EventActiveModel::BEFORE_SAVE) {
            $this->beforeSave();
        }
    }

    /**
     * Вызов события до валидации.
     * @return void
     */
    protected function beforeSave()
    {
        $timestamp = time();
        $this->owner->{$this->update} = $timestamp;

        if ($this->owner->isNewRecord()) {
            $this->owner->{$this->create} = $timestamp;
        }
    }
}
