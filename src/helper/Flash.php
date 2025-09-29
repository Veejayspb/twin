<?php

namespace twin\helper;

use twin\Twin;

class Flash
{
    /**
     * Название контейнера в сессии для хранения флеш-сообщений.
     */
    const STORAGE_NAME = 'flash';

    /**
     * Имеющиеся флеш-сообщения.
     * @var array
     */
    protected array $messages = [];

    /**
     * Экземпляр текущего класса.
     * @var static
     */
    private static self $instance;

    private function __construct()
    {
        $messages = Twin::app()->session->get(static::STORAGE_NAME, []);
        $this->messages = (array)$messages;
    }

    public function __destruct()
    {
        $session = Twin::app()->session;

        if (empty($this->messages)) {
            $session->delete(static::STORAGE_NAME);
        } else {
            $session->set(static::STORAGE_NAME, $this->messages);
        }
    }

    private function __clone() {}

    /**
     * Вернуть экземпляр текущего класса.
     * @return static
     */
    protected static function instance(): self
    {
        return static::$instance ??= new static;
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
            static::instance()->messages
        );
    }

    /**
     * Вернуть флеш-сообщение.
     * @param string $name - название
     * @param bool $clear - очистить сообщение
     * @return string|null - NULL, если флеш-сообщение отсутствует
     */
    public static function get(string $name, bool $clear = true): ?string
    {
        if (!static::has($name)) {
            return null;
        }

        $result = (string)static::instance()->messages[$name];

        if ($clear) {
            static::delete($name);
        }

        return $result;
    }

    /**
     * Добавить флеш-сообщение.
     * @param string $name - название
     * @param string $value - сообщение
     * @return void
     */
    public static function set(string $name, string $value): void
    {
        static::instance()->messages[$name] = $value;
    }

    /**
     * Удалить флеш-сообщение.
     * @param string $name - название
     * @return void
     */
    public static function delete(string $name): void
    {
        if (static::has($name)) {
            unset(static::instance()->messages[$name]);
        }
    }
}
