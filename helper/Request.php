<?php

namespace twin\helper;

use twin\helper\file\FileUploaded;

Request::$scheme = $_SERVER['REQUEST_SCHEME'];
Request::$host = $_SERVER['HTTP_HOST'];
Request::$url = $_SERVER['REQUEST_URI'];

class Request
{
    /**
     * Протокол.
     * @var string - http/https
     */
    public static $scheme;

    /**
     * Адрес хоста.
     * @var string - domain.ru
     */
    public static $host;

    /**
     * Текущий адрес.
     * @var string - /index.php?id=1
     */
    public static $url;

    /**
     * Проверка на AJAX-запрос.
     * @return bool
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * Вернуть значение GET-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed|null
     */
    public static function get(string $name, $default = null)
    {
        return $_GET[$name] ?? $default;
    }

    /**
     * Вернуть значение POST-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed|null
     */
    public static function post(string $name, $default = null)
    {
        return $_POST[$name] ?? $default;
    }

    /**
     * Вернуть значение REQUEST-параметра.
     * @param string $name - название параметра
     * @param mixed $default - значение по-умолчанию
     * @return mixed|null
     */
    public static function request(string $name, $default = null)
    {
        return $_REQUEST[$name] ?? $default;
    }

    /**
     * Вернуть данные массива $_FILES в виде набора объектов.
     * @param string $name - название параметра
     * @return array
     */
    public static function files(string $name): array
    {
        if (!array_key_exists($name, $_FILES)) {
            return [];
        }
        return FileUploaded::parse($_FILES[$name]);
    }
}
