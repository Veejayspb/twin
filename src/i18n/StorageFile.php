<?php

namespace twin\i18n;

use twin\helper\Alias;

class StorageFile implements StorageInterface
{
    /**
     * Директория с файлами-списками по-умолчанию.
     */
    const DEFAULT = '@self/l18n/locale';

    /**
     * Aлиас пути до директории с файлами-списками соответствий.
     * @var string
     */
    protected string $alias = self::DEFAULT;

    /**
     * Ранее использованные списки соответствий.
     * ключ - локаль
     * значение - список соответствий
     * @var array
     */
    protected array $lists = [];

    /**
     * @param string $alias
     */
    public function __construct(string $alias = self::DEFAULT)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function translate(string $message, string $locale): ?string
    {
        $list = $this->getListCached($locale);
        return $list[$message] ?? null;
    }

    /**
     * Список соответствий.
     * @param string $locale
     * @return array
     */
    protected function getList(string $locale): array
    {
        $path = $this->getPath($locale);

        if (!is_file($path)) {
            return [];
        }

        $list = require $path;
        return is_array($list) ? $list : [];
    }

    /**
     * Список соответствий (предварительно сохраненный).
     * @param string $locale
     * @return array
     */
    protected function getListCached(string $locale): array
    {
        return $this->lists[$locale] ??= $this->getList($locale);
    }

    /**
     * Путь до файла-списка.
     * @param string $locale
     * @return string
     */
    protected function getPath(string $locale): string
    {
        $alias = $this->alias . '/' . $locale . '.php';
        return Alias::get($alias);
    }
}
