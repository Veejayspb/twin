<?php

namespace twin\observer;

use SplSubject;
use twin\Twin;

class DateObserver extends AbstractObserver
{
    /**
     * Название свойства объекта для сохранения в него времени.
     * @var string
     */
    public string $property;

    /**
     * Формат даты.
     * @var string
     */
    public string $format = 'U';

    /**
     * {@inheritdoc}
     */
    public function update(SplSubject $subject): void
    {
        $owner = $subject->getOwner();
        $date = Twin::date()->format($this->format);
        $owner->{$this->property} = $date;
    }
}
