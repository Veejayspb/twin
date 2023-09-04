<?php

namespace twin\helper;

/**
 * Класс для работы с заголовками, которые отправляет сервер в ответе.
 *
 * Class Header
 */
class Header
{
    const PATTERN = '/^(.+?): (.+)$/';

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
            if (!preg_match(static::PATTERN, $item, $matches)) {
                continue;
            }

            $name = $matches[1];
            $value = $matches[2];
            $result[$name] = $value;
        }

        return $result;
    }
}
