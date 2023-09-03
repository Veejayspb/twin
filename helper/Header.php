<?php

namespace twin\helper;

/**
 * Класс для работы с заголовками, которые отправляет сервер в ответе.
 *
 * Class Header
 */
class Header
{
    /**
     * Экземпляр объекта.
     * @var static
     */
    protected static $instance;

    /**
     * Список заголовков по-умолчанию.
     * @var array
     */
    protected $default;

    private function __construct()
    {
        $this->default = $this->getList();
    }

    private function __clone() {}

    private function __wakeup() {}

    /**
     * Вернуть экземпляр объекта.
     * @return static
     */
    public static function instance(): self
    {
        return static::$instance = static::$instance ?: new static;
    }

    /**
     * Добавить заголовок.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function add(string $name, string $value): void
    {
        header("$name: $value");
    }

    /**
     * Удалить заголовок.
     * @param string $name
     * @return void
     */
    public function remove(string $name): void
    {
        header_remove($name);
    }

    /**
     * Удалить все заголовки.
     * @return void
     */
    public function clear(): void
    {
        $items = $this->getList();

        foreach ($items as $name => $value) {
            $this->remove($name);
        }
    }

    /**
     * Сбросить заголовки по-умолчанию.
     * @return void
     */
    public function reset(): void
    {
        $this->clear();
        $this->addDefault();
    }

    /**
     * Получить значение заголовка.
     * @param string $name
     * @return string|null
     */
    public function get(string $name): ?string
    {
        $items = $this->getList();
        return $items[$name] ?? null;
    }

    /**
     * Список заголовков.
     * @return array
     */
    public function getList(): array
    {
        $items = headers_list();
        $result = [];

        foreach ($items as $item) {
            if (!preg_match('/^(.+?): (.+)$/', $item, $matches)) {
                continue;
            }

            $name = $matches[1];
            $value = $matches[2];
            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Добавить заголовки по-умолчанию.
     * @return void
     */
    protected function addDefault(): void
    {
        foreach ($this->default as $name => $value) {
            $this->add($name, $value);
        }
    }
}
