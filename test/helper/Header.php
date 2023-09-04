<?php

namespace twin\test\helper;

final class Header extends \twin\helper\Header
{
    /**
     * Список заголовков.
     * @var array
     */
    private $_headers = [];

    /**
     * {@inheritdoc}
     */
    public function add(string $name, string $value): void
    {
        $this->_headers[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $name): void
    {
        if (array_key_exists($name, $this->_headers)) {
            unset($this->_headers[$name]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): ?string
    {
        return $this->_headers[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(): array
    {
        return $this->_headers;
    }
}
