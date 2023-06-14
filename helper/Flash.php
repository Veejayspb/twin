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
     * Экземпляр текущего класса.
     * @var self
     */
    private static $instance;

    private function __construct()
    {
        $session = $this->getSession();

        if (!$session) {
            return;
        }

        $this->messages = (array)$session->get(self::STORAGE_NAME);
    }

    public function __destruct()
    {
        $session = $this->getSession();

        if (!$session) {
            return;
        }

        if (empty($this->messages)) {
            $session->delete(self::STORAGE_NAME);
        } else {
            $session->set(self::STORAGE_NAME, $this->messages);
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
        if (!self::has($name)) {
            return null;
        }

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
     * @return Session|null
     */
    private function getSession()
    {
        return Twin::app()->getComponent(Session::class);
    }
}
