<?php

namespace twin\helper;

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
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
    }

    /**
     * Сравнить пароль с хэшем.
     * @param string $password - пароль
     * @param string $hash - хэш пароля
     * @return bool
     */
    public static function check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
