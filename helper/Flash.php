<?php

namespace twin\helper;

use twin\session\Session;
use twin\Twin;

final class Flash
{
    /**
     * Название контейнера в сессии для хранения флеш-сообщений.
     */
    const STORAGE_NAME = 'flash';

    /**
     * Имеющиеся флеш-сообщения.
     * @var array
     */
    private $messages = [];

    /**
     * Экземпляр сессии.
     * @var Session
     */
    private $session;

    /**
     * Экземпляр текущего класса.
     * @var self
     */
    private static $instance;

    private function __construct()
    {
        $this->session = $this->getSession();
        $this->messages = (array)$this->session->get(self::STORAGE_NAME);
    }

    public function __destruct()
    {
        if (empty($this->messages)) {
            $this->session->delete(self::STORAGE_NAME);
        } else {
            $this->session->set(self::STORAGE_NAME, $this->messages);
        }
    }

    private function __clone() {}

    private function __wakeup() {}

    /**
     * Вернуть экземпляр текущего класса.
     * @return self
     */
    private static function instance(): self
    {
        return self::$instance = self::$instance ?: new self;
    }

    /**
     * Имеется ли флеш-сообщение.
     * @param string $name - название
     * @return bool
     */
    public static function has(string $name): bool
    {
        return array_key_exists(
            $name,
            self::instance()->messages
        );
    }

    /**
     * Вернуть флеш-сообщение.
     * @param string $name - название
     * @param bool $clear - очистить сообщение
     * @return string|null - NULL, если флеш-сообщение отсутствует
     */
    public static function get(string $name, bool $clear = true)
    {
        if (!self::has($name)) return false;

        $result = (string)self::instance()->messages[$name];

        if ($clear) {
            self::delete($name);
        }

        return $result;
    }

    /**
     * Добавить флеш-сообщение.
     * @param string $name - название
     * @param string $value - сообщение
     * @return void
     */
    public static function set(string $name, string $value)
    {
        self::instance()->messages[$name] = $value;
    }

    /**
     * Удалить флеш-сообщение.
     * @param string $name - название
     * @return void
     */
    public static function delete(string $name)
    {
        if (self::has($name)) {
            unset(self::instance()->messages[$name]);
        }
    }

    /**
     * Вернуть экземпляр сессии.
     * @return Session
     */
    private function getSession()
    {
        return Twin::app()->session;
    }
}
