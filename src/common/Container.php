<?php

namespace twin\common;

use Psr\Container\ContainerInterface;

/**
 * DI контейнер с компонентами.
 *
 * $c = new Container;
 * $c->set(Component::class, fn() => new Component);
 * $c->set('anyString', function ($c) {
 *     return new Component;
 * });
 * $c->get(Component::class);
 * $c->get('anyString');
 *
 * Class Container
 */
class Container implements ContainerInterface
{
    /**
     * Конструкторы компонентов.
     * @var array
     */
    protected array $definitions = [];

    /**
     * Экземпляры компонентов.
     * @var array
     */
    protected array $instances = [];

    /**
     * Регистрация компонента.
     * @param string $id - идентификатор
     * @param callable $definition - конструктор компонента
     * @return void
     */
    public function set(string $id, callable $definition): void
    {
        $this->definitions[$id] = $definition;

        if (array_key_exists($id, $this->instances)) {
            unset($this->instances[$id]);
        }
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->definitions)) {
            throw new Exception(500, "Unknown definition: $id");
        }

        $definition = $this->definitions[$id];
        $instance = $definition($this);
        return $this->instances[$id] = $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }
}
