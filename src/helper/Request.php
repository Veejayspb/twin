<?php

namespace twin\helper;

Request::$scheme = $_SERVER['REQUEST_SCHEME'] ?? null;
Request::$host = $_SERVER['HTTP_HOST'] ?? null;
Request::$url = $_SERVER['REQUEST_URI'] ?? null;
Request::$ip = $_SERVER['REMOTE_ADDR'] ?? null;
Request::$method = $_SERVER['REQUEST_METHOD'] ?? null;
Request::$headers = function_exists('getallheaders') ? getallheaders() : [];
Request::$timestamp = time();

class Request
{
    /**
     * Протокол.
     * @var string - http/https
     */
    public static string $scheme;

    /**
     * Адрес хоста.
     * @var string - domain.ru
     */
    public static string $host;

    /**
     * Текущий адрес.
     * @var string - /index.php?id=1
     */
    public static string $url;

    /**
     * IP адрес.
     * @var string
     */
    public static string $ip;

    /**
     * Метод.
     * @var string - GET, POST, PUT, DELETE
     */
    public static string $method;

    /**
     * Заголовки.
     * @var array
     */
    public static array $headers = [];

    /**
     * Время запроса.
     * @var int
     */
    public static int $timestamp;

    /**
     * Проверка на AJAX-запрос.
     * @return bool
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * Приложение запущено из командной строки.
     * @return bool
     */
    public static function isConsole(): bool
    {
        if (defined('STDIN')) {
            return true;
        }

        if (empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Вернуть значение GET-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed
     */
    public static function get(string $name, mixed $default = null): mixed
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * Вернуть значение POST-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed
     */
    public static function post(string $name, mixed $default = null): mixed
    {
        return $_POST[$name] ?? $default;
    }

    /**
     * Вернуть значение REQUEST-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed
     */
    public static function request(string $name, mixed $default = null): mixed
    {
        return $_REQUEST[$name] ?? $default;
    }

    /**
     * Вернуть данные массива $_FILES.
     * @param string $name - название параметра $_FILES[$name]
     * @return array
     */
    public static function files(string $name): array
    {
        return $_FILES[$name] ?? [];
    }
}
