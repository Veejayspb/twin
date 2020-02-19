<?php

namespace twin\helper;

use twin\common\Exception;

final class Password
{
    /**
     * Сгенерировать хэш, соответствующий указанному паролю.
     * @param string $password - пароль
     * @param int $cost - значение (4-31), используемое алгоритмом Blowfish
     * @return string
     * @throws Exception
     */
    public static function hash(string $password, int $cost = 13): string
    {
        $salt = static::generateSalt($cost);
        return crypt($password, $salt);
    }

    /**
     * Сравнить пароль с хэшем.
     * @param string $password - пароль
     * @param string $hash - хэш пароля
     * @return bool
     */
    public static function check(string $password, string $hash): bool
    {
        if (!is_string($password) || $password === '') {
            return false;
        }
        $test = crypt($password, $hash);
        if (!is_string($test) || strlen($test) < 32) {
            return false;
        }
        return self::compare($test, $hash);
    }

    /**
     * Сгенерировать случайную строку/пароль.
     * @param int $min - минимальная длина
     * @param int $max - максимальная длина
     * @param bool $upperCase - использовать верхний регистр
     * @param bool $numbers - использовать цифры
     * @param bool $specialChars - использовать специальные символы
     * @return string
     */
    public static function generatePassword(int $min = 8, int $max = 8, bool $upperCase = false, bool $numbers = true, bool $specialChars = false): string
    {
        $length = rand($min, $max);
        $selection = 'abcdefghijklmnopqrstuvwxyz';

        if ($numbers) {
            $selection.= '1234567890';
        }

        if ($specialChars) {
            $selection.= '!@;#$%&[]{}?|';
        }

        $password = '';
        for($i = 0; $i < $length; $i++) {
            $password.= $upperCase ? (rand(0,1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];
        }
        return $password;
    }

    /**
     * Сгенерировать соль.
     * @param int $cost - значение (4-31), используемое алгоритмом Blowfish
     * @return string
     * @throws Exception
     */
    private static function generateSalt(int $cost = 13): string
    {
        $cost = (int)$cost;
        if ($cost < 4 || 31 < $cost) {
            throw new Exception(500, 'Cost value must be between 4 and 31');
        }
        $randomBase64 = static::generatePassword(22, 22, true, true);
        return '$2a$' . $cost . '$' . $randomBase64;
    }

    /**
     * Сравнить пару хэшированных паролей.
     * @param string $a - первая строка с хэшем
     * @param string $b - вторая строка с хэшем
     * @return bool
     */
    private static function compare(string $a, string $b): bool
    {
        if (!is_string($a) || !is_string($b)) return false;

        $mb = function_exists('mb_strlen');
        $length = $mb ? mb_strlen($a, '8bit') : strlen($a);
        if ($length !== ($mb ? mb_strlen($b, '8bit') : strlen($b))) {
            return false;
        }

        $check = 0;
        for($i = 0; $i < $length; $i++) {
            $check |= (ord($a[$i]) ^ ord($b[$i]));
        }

        return $check === 0;
    }
}
