<?php

namespace twin\event;

use twin\model\Model;

class EventModel extends Event
{
    const BEFORE_VALIDATE = 'before-validate';
    const AFTER_VALIDATE = 'after-validate';

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
}
