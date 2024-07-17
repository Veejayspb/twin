<?php

namespace twin\helper;

use twin\common\Exception;
use twin\criteria\Criteria;
use twin\widget\PaginationWidget;

/**
 * Class Pagination
 * @property int $total
 * @property int $page
 * @property int $size
 * @property-read int $offset - отступ
 * @property-read int $amount - кол-во страниц
 * @property-read int $from - порядковый номер первого отображаемого элемента
 * @property-read int $to - порядковый номер последнего отображаемого элемента
 */
class Pagination
{
    const DEFAULT_SIZE = 10;

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
    protected $size = self::DEFAULT_SIZE;

    /**
     * @param int $total
     * @param int $page
     * @param int $size
     */
    public function __construct(int $total, int $page, int $size = self::DEFAULT_SIZE)
    {
        $this->setTotal($total);
        $this->setPage($page);
        $this->setSize($size);
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
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        $parameters = [
            'total',
            'page',
            'size',
        ];

        if (in_array($name, $parameters)) {
            $method = 'set' . ucfirst($name);
            $this->$method($value);
        }
    }

    /**
     * Применить лимит и отступ к выборке.
     * @param Criteria $criteria
     * @return void
     */
    public function apply(Criteria $criteria): void
    {
        $criteria->offset = $this->offset;
        $criteria->limit = $this->size;
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
     * Установить общее кол-во записей.
     * @param int $value
     * @return static
     */
    protected function setTotal(int $value): self
    {
        $this->total = $value <= 0 ? 0 : $value;
        return $this;
    }

    /**
     * Установить номер страницы.
     * @param int $value
     * @return static
     */
    protected function setPage(int $value): self
    {
        $this->page = $value <= 1 ? 1 : $value;
        return $this;
    }

    /**
     * Установить размер страницы.
     * @param int $value
     * @return static
     */
    protected function setSize(int $value): self
    {
        $this->size = $value <= 1 ? 1 : $value;
        return $this;
    }

    /**
     * Вернуть отступ.
     * @return int
     */
    protected function getOffset(): int
    {
        return $this->size * ($this->page - 1);
    }

    /**
     * Подсчитать кол-во страниц.
     * @return int
     */
    protected function getAmount(): int
    {
        return ceil($this->total / $this->size);
    }

    /**
     * Отображение порядкового номера первого отображаемого элемента.
     * @return int
     */
    protected function getFrom(): int
    {
        return ($this->page - 1) * $this->size + 1;
    }

    /**
     * Отображение порядкового номера последнего отображаемого элемента.
     * @return int
     */
    protected function getTo(): int
    {
        $result = $this->page * $this->size;
        return min($this->total, $result);
    }
}
