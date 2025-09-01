<?php

use test\helper\BaseTestCase;
use twin\i18n\StorageFile;

final class StorageFileTest extends BaseTestCase
{
    public function testTranslate()
    {
        $storage = new StorageFile(__DIR__);

        $actual = $storage->translate('test', 'ru');
        $this->assertSame('тест', $actual);

        $actual = $storage->translate('undefined', 'ru');
        $this->assertNull($actual);

        $actual = $storage->translate('test', 'undefined');
        $this->assertNull($actual);
    }

    public function testGetList()
    {
        $storage = $this->getStorage();

        $actual = $storage->getList('ru');
        $this->assertSame(['test' => 'тест'], $actual);

        $actual = $storage->getList('undefined');
        $this->assertSame([], $actual);
    }

    public function testGetPath()
    {
        $storage = $this->getStorage();

        $locals = [
            'ru',
            'en',
            'undefined',
        ];

        foreach ($locals as $local) {
            $actual = $storage->getPath($local);
            $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . $local . '.php', $actual);
        }
    }

    protected function getStorage()
    {
        return new class extends StorageFile
        {
            const DEFAULT = '@test/unit/i18n';

            public string $alias = self::DEFAULT;

            public function __construct(string $alias = self::DEFAULT)
            {
                parent::__construct($alias);
            }

            public function getList(string $locale): array
            {
                return parent::getList($locale);
            }

            public function getPath(string $locale): string
            {
                return parent::getPath($locale);
            }
        };
    }
}
