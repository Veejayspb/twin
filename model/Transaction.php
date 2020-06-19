<?php

namespace twin\model;

use twin\common\Exception;
use twin\db\sql\Sql;

/**
 * Class Transaction
 * @package twin\model
 *
 * @property bool $running
 * @property bool $error
 */
final class Transaction
{
    /**
     * Запущена ли транзакция.
     * @var bool
     */
    protected $running = false;

    /**
     * Наличие ошибок при сохранении/удалении модели.
     * @var bool
     */
    protected $error = false;

    /**
     * Компонент для работы с БД
     * @var Sql
     */
    protected $component;

    /**
     * Массив транзакций для каждого из компонентов.
     * @var static[]
     */
    private static $multitone = [];

    /**
     * @param Sql $component - компонент для работы с БД
     * @throws Exception
     */
    private function __construct(Sql $component)
    {
        if (!is_subclass_of($component, Sql::class)) {
            throw new Exception(500, 'Component that using transaction must extends ' . Sql::class);
        }
        $this->component = $component;
    }

    private function __clone() {}

    private function __wakeup() {}

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Вернуть экземпляр объекта с транзакцией.
     * @param Sql $component - компонент для работы с БД
     * @return Transaction
     */
    public static function get(Sql $component): self
    {
        $class = get_class($component);
        if (!array_key_exists($class, static::$multitone)) {
            static::$multitone[$class] = new static($component);
        }
        return static::$multitone[$class];
    }

    /**
     * Начать транзакцию.
     * @return bool
     */
    public function begin(): bool
    {
        if (!$this->running) {
            $this->running = true;
            return $this->component->transactionBegin();
        }
        return false;
    }

    /**
     * Закончить транзакцию.
     * Применит все изменения БД, если не произошло ошибок.
     * @return bool
     */
    public function end(): bool
    {
        if ($this->running) {
            $this->running = false;
            $this->error = false;
            return $this->component->transactionCommit();
        }
        return true;
    }

    /**
     * Добавить ошибку.
     * Вызывается при ошибке сохранения/удаления модели.
     * Откатит предыдущие изменения и предотвратит дальнейшие изменения БД до вызова end().
     * @return bool
     */
    public function error(): bool
    {
        if ($this->running) {
            $this->error = true;
            return $this->component->transactionRollback();
        }
        return true;
    }
}
