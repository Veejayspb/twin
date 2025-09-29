<?php

namespace twin\session;

use twin\helper\Cookie;

class Identity
{
    /**
     * Название параметра сессии для хранения идентификатора пользователя.
     */
    const IDENTITY = 'identity';

    /**
     * Параметр куки для хранения токена сессии.
     */
    const TOKEN = 'token';

    /**
     * Идентификатор пользователя.
     * @var int|null
     */
    protected ?int $id = null;

    /**
     * Произвольная строка для генерации токена.
     * @var string
     */
    protected string $secretString;

    /**
     * Компонент с сессией.
     * @var Session
     */
    protected Session $session;

    /**
     * @param Session $session
     * @param string $secretString
     */
    public function __construct(Session $session, string $secretString)
    {
        $this->session = $session;
        $this->secretString = $secretString;
        $this->restoreId();
    }

    /**
     * Вернуть идентификатор пользователя.
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Является ли гостем (не аутентифицирован).
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->id === null;
    }

    /**
     * Авторизация.
     * @param int $id - идентификатор пользователя
     * @param int $expire - запомнить пользователя на указанное время
     * @return void
     */
    public function login(int $id, int $expire = 0): void
    {
        $this->id = $id;
        $token = $this->createToken($id);

        Cookie::set(static::IDENTITY, $id, $expire);
        Cookie::set(static::TOKEN, $token, $expire);
    }

    /**
     * Деавторизация.
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();

        Cookie::delete(static::TOKEN);
        Cookie::delete(static::IDENTITY);
    }

    /**
     * Сгенерировать уникальный токен для возобновления сессии между визитами.
     * @param int $id - идентификатор пользователя
     * @return string
     */
    protected function createToken(int $id): string
    {
        $str = md5($id) . $this->secretString;
        return md5($str);
    }

    /**
     * Проверить валидность токена.
     * @param string $token - токен
     * @param int $id - идентификатор пользователя
     * @return bool
     */
    protected function checkToken(string $token, int $id): bool
    {
        return $token == $this->createToken($id);
    }

    /**
     * Восстановить идентификатор пользователя из сессии/куки.
     * @return void
     */
    protected function restoreId(): void
    {
        $id = $this->session->get(static::IDENTITY);

        if ($id !== null) {
            $this->id = $id;
            return;
        }

        $id = Cookie::get(static::IDENTITY);
        $token = Cookie::get(static::TOKEN);

        if ($id !== null && $this->checkToken($token, $id)) {
            $this->id = $id;
        }
    }
}
