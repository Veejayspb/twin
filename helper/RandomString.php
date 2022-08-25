<?php

namespace twin\helper;

/**
 * Генерация случайной строки (пароля).
 */
class RandomString
{
    const LOWER = 'abcdefghijklmnopqrstuvwxyz';
    const UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const DIGIT = '0123456789';
    const SPECIAL = '!@;#$%&[]{}?|';

    /**
     * Именованные списки допустимых символов.
     * ключ - название списка
     * значение - строка символов
     * @var array
     */
    protected $collection = [];

    public function __construct()
    {
        $this->setDefault();
    }

    /**
     * Использовать строчные латинские буквы.
     * @param bool $value
     * @return static
     */
    public function useLower(bool $value = true): self
    {
        $this->collection['lower'] = $value ? self::LOWER : '';
        return $this;
    }

    /**
     * Использовать заглавные латинские буквы.
     * @param bool $value
     * @return static
     */
    public function useUpper(bool $value = true): self
    {
        $this->collection['upper'] = $value ? self::UPPER : '';
        return $this;
    }

    /**
     * Использовать цифры.
     * @param bool $value
     * @return static
     */
    public function useDigit(bool $value = true): self
    {
        $this->collection['digit'] = $value ? self::DIGIT : '';
        return $this;
    }

    /**
     * Использовать спецсимволы.
     * @param bool $value
     * @return static
     */
    public function useSpecial(bool $value = true): self
    {
        $this->collection['special'] = $value ? self::SPECIAL : '';
        return $this;
    }

    /**
     * Использовать произвольные символы.
     * @param string $value - список символов в строку
     * @return static
     */
    public function useCustom(string $value): self
    {
        $key = md5($value);
        $this->collection[$key] = $value;
        return $this;
    }

    /**
     * Очистить все списки.
     * @return static
     */
    public function clear(): self
    {
        $this->collection = [];
        return $this;
    }

    /**
     * Генерация строки.
     * @param int $min - минимальная длина
     * @param int $max - максимальная длина
     * @return string
     */
    public function run(int $min, int $max): string
    {
        $min = abs($min);
        $max = abs($max);

        // Если макс < мин, то поменять их местами
        if ($max < $min) {
            list($min, $max) = [$max, $min];
        }

        $length = mt_rand($min, $max);
        $symbols = $this->getSymbols();
        $symbolsAmount = strlen($symbols);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = mt_rand(0, $symbolsAmount);
            $result .= $symbols[$randomIndex];
        }

        return $result;
    }

    /**
     * Установить коллекции символов по-умолчанию.
     * @return void
     */
    protected function setDefault()
    {
        $this
            ->clear()
            ->useLower()
            ->useDigit();
    }

    /**
     * Полный список символов в строку.
     * @return string
     */
    protected function getSymbols(): string
    {
        $result = implode('', $this->collection);

        if ($result != '') {
            return $result;
        }

        $this->setDefault();
        return implode('', $this->collection);
    }
}
