<?php

namespace twin\observer;

use SplSubject;
use twin\helper\StringHelper;

class SlugObserver extends AbstractObserver
{
    public array $events = [
        'test',
    ];

    /**
     * Название свойства из которого берем текст.
     * @var string
     */
    public string $from;

    /**
     * Название свойства, куда кладём транслитерацию.
     * @var string
     */
    public string $to;

    /**
     * Заполнить поле для транслита даже если оно непустое.
     * @var bool
     */
    public bool $force = false;

    /**
     * {@inheritdoc}
     */
    public function update(SplSubject $subject): void
    {
        $owner = $subject->getOwner();

        if ($this->force === false && $owner->{$this->to} !== null) {
            return;
        }

        $value = $owner->{$this->from};

        if (!is_string($value)) {
            return;
        }

        $slug = StringHelper::slug($value);
        $owner->{$this->to} = $slug;
    }
}
