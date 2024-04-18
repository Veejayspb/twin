<?php

namespace twin\event;

use twin\model\Model;

class EventModel extends Event
{
    const BEFORE_VALIDATE = 'before-validate';
    const AFTER_VALIDATE = 'after-validate';
    const BEFORE_SAVE = 'before-save';
    const AFTER_SAVE = 'after-save';
    const BEFORE_DELETE = 'before-delete';
    const AFTER_DELETE = 'after-delete';

    /**
     * {@inheritdoc}
     */
    public function __construct(Model $owner)
    {
        parent::__construct($owner);
    }

    /**
     * Событие, вызываемое перед валидацией модели.
     * @return void
     */
    public function beforeValidate()
    {
        $this->notify(self::BEFORE_VALIDATE);
    }

    /**
     * Событие, вызываемое после валидации модели.
     * @return void
     */
    public function afterValidate()
    {
        $this->notify(self::AFTER_VALIDATE);
    }

    /**
     * Событие, вызываемое перед сохранением модели.
     * @return void
     */
    public function beforeSave()
    {
        $this->notify(self::BEFORE_SAVE);
    }

    /**
     * Событие, вызываемое после сохранения модели.
     * @return void
     */
    public function afterSave()
    {
        $this->notify(self::AFTER_SAVE);
    }

    /**
     * Событие, вызываемое перед удалением модели.
     * @return void
     */
    public function beforeDelete()
    {
        $this->notify(self::BEFORE_DELETE);
    }

    /**
     * Событие, вызываемое после удаления модели.
     * @return void
     */
    public function afterDelete()
    {
        $this->notify(self::AFTER_DELETE);
    }
}
