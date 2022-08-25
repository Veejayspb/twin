<?php

namespace twin\helper;

use twin\common\Exception;

class Password
{
    /**
     * Сгенерировать хэш, соответствующий указанному паролю.
     * @param string $password - пароль
     * @param int $cost - значение (4-31), используемое алгоритмом Blowfish
     * @return string
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
        if ($password == '') {
            return false;
        }

        $test = crypt($password, $hash);

        if (!is_string($test) || strlen($test) < 32) {
            return false;
        }

        return self::compare($test, $hash);
    }

    /**
     * Сгенерировать соль.
     * @param int $cost - значение (4-31), используемое алгоритмом Blowfish
     * @return string
     * @throws Exception
     */
    private static function generateSalt(int $cost = 13): string
    {
        if ($cost < 4 || 31 < $cost) {
            throw new Exception(500, 'Cost value must be between 4 and 31');
        }

        $randomBase64 = (new RandomString)->useUpper()->run(22, 22);
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
