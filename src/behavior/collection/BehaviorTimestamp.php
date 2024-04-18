<?php

namespace twin\behavior\collection;

use twin\behavior\BehaviorModel;
use twin\event\EventModel;

class BehaviorTimestamp extends BehaviorModel
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
        if ($event == EventModel::BEFORE_SAVE) {
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
