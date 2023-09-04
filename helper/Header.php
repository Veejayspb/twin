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
     * Добавить несколько заголовков.
     * @param array $headers
     * @return void
     */
    public function addMultiple(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->add($name, $value);
        }
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
     * Удалить несколько заголовков.
     * @param array $names
     * @return void
     */
    public function removeMultiple(array $names): void
    {
        foreach ($names as $name) {
            $this->remove($name);
        }
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
     * @param string $name - название заголовка в любом регистре
     * @return string|null
     */
    public function get(string $name): ?string
    {
        $name = strtolower($name);
        $items = $this->getList();

        foreach ($items as $key => $value) {
            $key = strtolower($key);

            if ($key == $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Список заголовков.
     * @return array
     */
    public function getList(): array
    {
        $items = $this->getRawList();
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

    /**
     * Необработанный список заголовков.
     * @return array
     */
    protected function getRawList(): array
    {
        return headers_list();
    }
}
