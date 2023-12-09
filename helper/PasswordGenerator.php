<?php

namespace twin\helper;

class PasswordGenerator
{
    const LOWER = 'abcdefghijklmnopqrstuvwxyz';
    const UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const DIGIT = '0123456789';
    const SPECIAL = '!@;#$%&[]{}?|.,/\\';

    /**
     * Добавленные коллекции символов.
     * ключ - хэш коллекции
     * значение - список символов в строку
     * @var array
     */
    protected $collections = [];

    public function __construct()
    {
        $this
            ->addCollection(static::LOWER)
            ->addCollection(static::UPPER)
            ->addCollection(static::DIGIT);
    }

    /**
     * Сгенерировать пароль указанной длины.
     * Он будет содержать равномерное кол-во символов из каждой коллекции.
     * @param int $length - длина пароля
     * @return string
     */
    public function fixed(int $length): string
    {
        if (empty($this->collections)) {
            return '';
        }

        $result = [];

        for ($i = 0; $i < $length; $i++) {
            $collection = $this->getCollection($i);
            $result[] = $this->getRandomChar($collection);
        }

        shuffle($result);
        return implode('', $result);
    }

    /**
     * Генерация строки с паролем.
     * @param int $min - минимальная длина
     * @param int $max - максимальная длина
     * @return string
     */
    public function range(int $min, int $max): string
    {
        $min = abs($min);
        $max = abs($max);

        // Если макс < мин, то поменять их местами
        if ($max < $min) {
            list($min, $max) = [$max, $min];
        }

        $length = mt_rand($min, $max);
        return $this->fixed($length);
    }

    /**
     * Добавить коллекцию символов.
     * @param string $collection - список символов в строку
     * @return static
     */
    public function addCollection(string $collection): self
    {
        $hash = $this->getCollectionHash($collection);
        $this->collections[$hash] = $collection;
        return $this;
    }

    /**
     * Удалить коллекцию.
     * @param string $collection - список символов в строку
     * @return static
     */
    public function removeCollection(string $collection): self
    {
        $hash = $this->getCollectionHash($collection);

        if (array_key_exists($hash, $this->collections)) {
            unset($this->collections[$hash]);
        }

        return $this;
    }

    /**
     * Очистить список коллекций.
     * @return static
     */
    public function clearCollections(): self
    {
        $this->collections = [];
        return $this;
    }

    /**
     * Вернуть хэш коллекции для использования в качестве ключа.
     * @param string $collection - список символов в строку
     * @return string
     */
    protected function getCollectionHash(string $collection): string
    {
        return md5($collection);
    }

    /**
     * Вернуть коллекцию по ее порядковому индексу.
     * @param int $index
     * @return string|null
     */
    protected function getCollection(int $index): ?string
    {
        if (empty($this->collections)) {
            return null;
        }

        $index = abs($index);
        $collection = reset($this->collections);

        for ($i = 0; $i < $index; $i++) {
            $collection = next($this->collections);

            if ($collection === false) {
                $collection = reset($this->collections);
            }
        }

        return $collection;
    }

    /**
     * Вернуть случайный символ из переданной коллекции.
     * @param string $collection - список символов в строку
     * @return string
     */
    protected function getRandomChar(string $collection): string
    {
        $length = mb_strlen($collection);

        if ($length === 0) {
            return '';
        }

        $index = mt_rand(0, $length - 1);
        return $collection[$index];
    }
}
