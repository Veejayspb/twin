<?php

use test\helper\BaseTestCase;
use twin\i18n\I18n;
use twin\i18n\StorageInterface;

final class I18nTest extends BaseTestCase
{
    public function testAddStorage()
    {
        $storage = $this->getStorage();

        $i18n = $this->getI18n();
        $i18n->addStorage($storage);

        $this->assertSame([$storage], $i18n->storages);
    }

    public function testClearStorages()
    {
        $storage = $this->getStorage();
        $i18n = $this->getI18n();
        $i18n->storages = [$storage];
        $i18n->clearStorages();

        $this->assertSame([], $i18n->storages);
    }

    public function testTranslate()
    {
        $original = 'text';
        $one = 'один';
        $two = 'два';

        $storageOne = $this->getStorage();
        $storageOne->translate = $one;

        $storageTwo = $this->getStorage();
        $storageTwo->translate = $two;

        $i18n = $this->getI18n(I18n::RUSSIAN);
        $i18n->addStorage($storageOne);
        $i18n->addStorage($storageTwo);

        // При наличии перевода в 2-х хранилищах приоритет отдается последнему
        $actual = $i18n->translate($original);
        $this->assertSame($two, $actual);

        // При отсутствии перевода во 2-м хранилище берем перевод из 1-го
        $storageTwo->translate = null;
        $actual = $i18n->translate($original);
        $this->assertSame($one, $actual);

        // При отсутствии перевода в каком-либо хранилище оставляем текст в неизменном виде
        $storageOne->translate = null;
        $actual = $i18n->translate($original);
        $this->assertSame($original, $actual);
    }

    /**
     * @return I18n
     */
    protected function getI18n(string $locale = I18n::DEFAULT)
    {
        $i18n = new class extends I18n
        {
            public array $storages = [];
        };

        $i18n->locale = $locale;
        $i18n->clearStorages();
        return $i18n;
    }

    /**
     * @return StorageInterface
     */
    protected function getStorage()
    {
        return new class implements StorageInterface
        {
            public ?string $translate = null;

            public function translate(string $message, string $locale): ?string
            {
                return $this->translate;
            }
        };
    }
}
