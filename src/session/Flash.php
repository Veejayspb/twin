<?php

namespace twin\session;

/**
 * Класс для управления флеш-сообщениями.
 *
 * Class Flash
 */
class Flash
{
    /**
     * Название контейнера в сессии для хранения флеш-сообщений.
     */
    const STORAGE_NAME = 'flash';

    /**
     * Компонент с сессией.
     * @var Session
     */
    protected Session $session;

    /**
     * Имеющиеся флеш-сообщения.
     * @var array
     */
    protected array $messages = [];

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $messages = $session->get(static::STORAGE_NAME, []);
        $this->messages = (array)$messages;
    }

    public function __destruct()
    {
        if (empty($this->messages)) {
            $this->session->delete(static::STORAGE_NAME);
        } else {
            $this->session->set(static::STORAGE_NAME, $this->messages);
        }
    }

    /**
     * Имеется ли флеш-сообщение.
     * @param string $name - название
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->messages);
    }

    /**
     * Вернуть флеш-сообщение.
     * @param string $name - название
     * @param bool $clear - очистить сообщение
     * @return string|null - NULL, если флеш-сообщение отсутствует
     */
    public function get(string $name, bool $clear = true): ?string
    {
        if (!$this->has($name)) {
            return null;
        }

        $result = (string)$this->messages[$name];

        if ($clear) {
            $this->delete($name);
        }

        return $result;
    }

    /**
     * Добавить флеш-сообщение.
     * @param string $name - название
     * @param string $value - сообщение
     * @return void
     */
    public function set(string $name, string $value): void
    {
        $this->messages[$name] = $value;
    }

    /**
     * Удалить флеш-сообщение.
     * @param string $name - название
     * @return void
     */
    public function delete(string $name): void
    {
        $has = $this->has($name);

        if ($has) {
            unset($this->messages[$name]);
        }
    }
}
