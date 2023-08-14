<?php

namespace twin\helper;

use twin\common\Exception;
use twin\model\query\Query;
use twin\widget\PaginationWidget;

/**
 * Class Pagination
 * @property-read int $total
 * @property-read int $page
 * @property-read int $limit
 * @property-read int $offset - отступ
 * @property-read int $amount - кол-во страниц
 * @property-read int $from - порядковый номер первого отображаемого элемента
 * @property-read int $to - порядковый номер последнего отображаемого элемента
 */
class Pagination
{
    /**
     * Общее кол-во элементов.
     * @var int
     */
    protected $total;

    /**
     * Номер страницы.
     * @var int
     */
    protected $page = 1;

    /**
     * Лимит элементов на одну страницу.
     * @var int
     */
    protected $limit = 10;

    /**
     * @param int $total
     * @param int $page
     * @param int $limit
     */
    public function __construct(int $total, int $page, int $limit = 0)
    {
        $this->setTotal($total);
        $this->setPage($page);
        $this->setSize($limit);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        $parameters = [
            'offset',
            'amount',
            'from',
            'to',
        ];

        if (in_array($name, $parameters)) {
            $method = 'get' . ucfirst($name);
            return $this->$method();
        } else {
            return $this->$name;
        }
    }

    /**
     * Установить общее кол-во записей.
     * @param int $value
     * @return static
     */
    public function setTotal(int $value): self
    {
        $this->total = $value <= 0 ? 0 : $value;
        return $this;
    }

    /**
     * Установить номер страницы.
     * @param int $value
     * @return static
     */
    public function setPage(int $value): self
    {
        $this->page = $value <= 1 ? 1 : $value;
        return $this;
    }

    /**
     * Установить размер страницы.
     * @param int $value
     * @return static
     */
    public function setSize(int $value): self
    {
        $this->limit = $value <= 1 ? 1 : $value;
        return $this;
    }

    /**
     * Применить лимит и отступ к выборке на базе ActiveModel.
     * @param Query $query
     * @return Query
     */
    public function apply(Query $query): Query
    {
        return $query
            ->offset($this->offset)
            ->limit($this->limit);
    }

    /**
     * Виджет пагинатора.
     * @param array $properties - установить свойства виджета
     * @param string $class - класс виджета пагинатора
     * @return string
     * @throws Exception
     */
    public function widget(array $properties = [], string $class = PaginationWidget::class): string
    {
        if (!is_a($class, PaginationWidget::class, true)) {
            throw new Exception(500, "$class must extends " . PaginationWidget::class);
        }

        $properties['pagination'] = $this;
        $widget = new $class($properties); /* @var PaginationWidget $widget */
        return $widget->run();
    }

    /**
     * Вернуть отступ.
     * @return int
     */
    protected function getOffset(): int
    {
        return $this->limit * ($this->page - 1);
    }

    /**
     * Подсчитать кол-во страниц.
     * @return int
     */
    protected function getAmount(): int
    {
        return ceil($this->total / $this->limit);
    }

    /**
     * Отображение порядкового номера первого отображаемого элемента.
     * @return int
     */
    protected function getFrom(): int
    {
        return ($this->page - 1) * $this->limit + 1;
    }

    /**
     * Отображение порядкового номера последнего отображаемого элемента.
     * @return int
     */
    protected function getTo(): int
    {
        $result = $this->page * $this->limit;
        return min($this->total, $result);
    }
}
