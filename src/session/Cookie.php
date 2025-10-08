<?php

namespace twin\session;

class Cookie
{
    /**
     * Префикс названий параметров.
     * @var string
     */
    public string $prefix = 'twin_';

    /**
     * Домен, для которого устанавливаются куки.
     * @var string|null
     */
    public ?string $domain = null;

    /**
     * Путь на сервере, для которого доступны куки.
     * @var string
     */
    public string $path = '/';

    /**
     * Если TRUE, куки отправляются только через HTTPS.
     * @var bool
     */
    public bool $secure = false;

    /**
     * Если TRUE, куки недоступны через JS.
     * @var bool
     */
    public bool $httpOnly = false;

    /**
     * Сохранить значение параметра в куки.
     * @param string $name - название
     * @param string $value - значение
     * @param int $expire - кол-во секунд, через которое истечет срок действия
     * @return bool
     */
    public function set(string $name, string $value, int $expire = 0): bool
    {
        $name = $this->getName($name);

        if ($expire > 0) {
            $expire += time();
        } else {
            $expire = 0;
        }

        $result = $this->setCookie($name, $value, $expire);

        if ($result) {
            $_COOKIE[$name] = $value;
        }

        return $result;
    }

    /**
     * Вернуть значение параметра из куки.
     * @param string $name - название параметра
     * @param string|null $default - значение по-умолчанию
     * @return string|null
     */
    public function get(string $name, ?string $default = null): ?string
    {
        $name = $this->getName($name);
        return $_COOKIE[$name] ?? $default;
    }

    /**
     * Удалить параметр из куки.
     * @param string $name - название параметра
     * @return bool
     */
    public function delete(string $name): bool
    {
        $name = $this->getName($name);
        $result = $this->setCookie($name, '', -1);

        if ($result && array_key_exists($name, $_COOKIE)) {
            unset($_COOKIE[$name]);
        }

        return $result;
    }

    /**
     * Вернуть название параметра с префиксом.
     * @param string $name - название параметра без префикса
     * @return string
     */
    protected function getName(string $name): string
    {
        return $this->prefix . $name;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @return bool
     */
    protected function setCookie(string $name, string $value, int $expire = 0): bool
    {
        return setcookie($name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }
}
