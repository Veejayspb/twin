<?php

namespace twin\i18n;

interface StorageInterface
{
    /**
     * Перевод.
     * @param string $message
     * @param string $locale
     * @return string|null
     */
    public function translate(string $message, string $locale): ?string;
}
