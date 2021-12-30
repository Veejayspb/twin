<?php

namespace twin\widget;

use twin\helper\Html;
use twin\helper\Pagination;
use twin\helper\Request;
use twin\helper\Url;

/**
 * Class Table
 * @property-read int $limit - кол-во элементов на одну страницу
 *
 * @todo: фильтрация
 */
abstract class Table extends Widget
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * Название GET-параметра для сортировки.
     */
    const SORT_PARAMETER = 'sort';

    /**
     * Список столбцов.
     * @var TableColumn[]
     */
    public $columns = [];

    /**
     * HTML-атрибуты таблицы.
     * @var array
     */
    public $htmlAttributes = [];

    /**
     * Название GET-параметра с номером страницы.
     * @var string
     */
    public $page = PaginationWidget::DEFAULT_PARAMETER;

    /**
     * Объект с хелпером-пагинатором.
     * @var Pagination
     */
    protected $pagination;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (array_key_exists('limit', $properties)) {
            $this->setLimit($properties['limit']);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == 'limit') {
            return $this->getPagination()->limit;
        } else {
            return $this->$name;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $pagination = $this->getPagination()->widget([
            'parameter' => $this->page,
        ]);

        $result = Html::tag('div', [], $this->statistics());
        $result.= $pagination;
        $result.= $this->table();
        $result.= $pagination;

        return $result;
    }

    /**
     * Разметка таблицы.
     * @return string
     */
    protected function table(): string
    {
        $result = Html::tagOpen('table', $this->htmlAttributes);
        $result.= $this->head();
        $result.= $this->body();
        $result.= Html::tagClose('table');

        return $result;
    }

    /**
     * Разметка заголовка таблицы.
     * @return string
     */
    protected function head(): string
    {
        list($sortName, $sortType) = $this->getSortParameter();

        $result = Html::tagOpen('thead');
        $result.= Html::tagOpen('tr');
        foreach ($this->columns as $column) {
            $name = $column->label;

            if ($column->sort) {
                if ($column->name == $sortName) {
                    $name.= $sortType == static::ASC ? ' &uarr;' : ' &darr;';
                }
                $type = $column->name == $sortName && $sortType == static::ASC ? static::DESC : static::ASC;
                $sortParameter = $this->generateSortParameter($column->name, $type);
                $url = Url::current([static::SORT_PARAMETER => $sortParameter]);
                $name = Html::a($url, $name);
            }

            $result.= Html::tag('th', [], $name);
        }
        $result.= Html::tagClose('tr');
        $result.= Html::tagClose('thead');

        return $result;
    }

    /**
     * Разметка тела таблицы.
     * @return string
     */
    protected function body(): string
    {
        $items = $this->getItems();

        $result = Html::tagOpen('thead');
        foreach ($items as $item) {
            $result.= $this->row($item);
        }
        $result.= Html::tagClose('thead');

        return $result;
    }

    /**
     * Разметка строки в теле таблицы.
     * @param mixed $item
     * @return string
     */
    protected function row($item): string
    {
        $result = Html::tagOpen('tr');
        foreach ($this->columns as $column) {
            $result.= Html::tagOpen('td');
            $result.= call_user_func($column->value, $item);
            $result.= Html::tagClose('td');
        }
        $result.= Html::tagClose('tr');

        return $result;
    }

    /**
     * Текст со статистикой.
     * @return string
     */
    protected function statistics(): string
    {
        $pagination = $this->getPagination();
        return "Показаны записи {$pagination->from}-{$pagination->to} из {$pagination->total}.";
    }

    /**
     * Извлечь текущие параметры сортировки из GET-параметра.
     * @return array
     * первый элемент - название столбца
     * второй элемент - тип сортировки
     */
    protected function getSortParameter(): array
    {
        $null = [null, null];

        $sort = Request::get(static::SORT_PARAMETER);
        if ($sort === null) return $null;

        $ascDesc = static::ASC . '|' . static::DESC;
        if (!preg_match("/^(.+)-($ascDesc)$/", $sort, $matches)) {
            return $null;
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * Сгенерировать значение GET-параметра для сортировки.
     * @param string $name - название столбца
     * @param string $type - тип сортировки
     * @return string
     */
    protected function generateSortParameter(string $name, string $type): string
    {
        return $name . '-' . $type;
    }

    /**
     * Поиск столбца по его названию.
     * @param string $name - название столбца
     * @return TableColumn|bool - FALSE, если столбец не найден
     */
    protected function getColumnByName(string $name)
    {
        foreach ($this->columns as $column) {
            if ($column->name == $name) {
                return $column;
            }
        }
        return false;
    }

    /**
     * Вернуть объект с пагинатором.
     * @return Pagination
     */
    protected function getPagination(): Pagination
    {
        if ($this->pagination !== null) {
            return $this->pagination;
        }

        return $this->pagination = new Pagination(
            $this->getTotal(),
            $this->getPageNumber()
        );
    }

    /**
     * Установить кол-во элементов на одну страницу.
     * @param int $value
     * @return void
     */
    protected function setLimit(int $value)
    {
        $this->getPagination()->setSize($value);
    }

    /**
     * Номер текущей страницы.
     * @return int
     */
    protected function getPageNumber(): int
    {
        return Request::get($this->page, 1);
    }

    /**
     * Подсчет общего кол-ва элементов.
     * @return int
     */
    abstract protected function getTotal(): int;

    /**
     * Вернуть массив данных для вывода.
     * @return array
     */
    abstract protected function getItems(): array;
}
