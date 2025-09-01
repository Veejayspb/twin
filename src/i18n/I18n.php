<?php

namespace twin\i18n;

use twin\common\Component;

class I18n extends Component
{
    const DEFAULT = self::ENGLISH;

    const ENGLISH = 'en';
    const RUSSIAN = 'ru';

    /**
     * Буквенный код языка на который осуществляется перевод.
     * @var string
     */
    public string $locale = self::DEFAULT;

    /**
     * Список хранилищ с переводами.
     * Последнее наиболее приоритетное.
     * @var StorageInterface[]
     */
    protected array $storages = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        $this->addStorage(new StorageFile);
    }

    /**
     * Добавить хранилище.
     * @param StorageInterface $storage
     * @return void
     */
    public function addStorage(StorageInterface $storage): void
    {
        $this->storages[] = $storage;
    }

    /**
     * Удалить все хранилища.
     * @return void
     */
    public function clearStorages(): void
    {
        $this->storages = [];
    }

    /**
     * Транслитерация сообщения.
     * Если нет соответствия, то текст останется в исходном виде.
     * @param string $message - текст на английском языке
     * @return string - текст, переведенный на целевой язык
     */
    public function translate(string $message): string
    {
        if ($this->locale == self::DEFAULT) {
            return $message;
        }

        $storages = array_reverse($this->storages);

        foreach ($storages as $storage) {
            $translate = $storage->translate($message, $this->locale);

            if ($translate !== null) {
                return $translate;
            }
        }

        return $message;
    }
}
