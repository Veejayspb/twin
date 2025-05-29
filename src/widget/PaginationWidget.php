<?php

namespace twin\widget;

use twin\common\Exception;
use twin\helper\Pagination;
use twin\helper\Url;

class PaginationWidget extends NavWidget
{
    /**
     * Название GET-параметра с номером страницы по-умолчанию.
     */
    const DEFAULT_PARAMETER = 'page';

    /**
     * CSS-класс неактивного пункта меню.
     * @var string
     */
    public $disabledClass = 'disabled';

    /**
     * Название GET-параметра с номером страницы.
     * @var string
     */
    public $parameter = self::DEFAULT_PARAMETER;

    /**
     * Отображать кнопки предыдущая/следующая страница.
     * @var bool
     */
    public $prevNext = true;

    /**
     * {@inheritdoc}
     */
    public array $htmlAttributes = [
        'class' => 'pagination',
    ];

    /**
     * @var Pagination
     */
    public $pagination;

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);

        if (!is_a($this->pagination, Pagination::class)) {
            throw new Exception(500, self::class . ' - required properties not specified: pagination');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        // Если страница одна/ноль, то не выводить пагинатор.
        if ($this->pagination->amount <= 1) {
            return '';
        }

        $this->items = $this->getItems();

        return parent::run();
    }

    /**
     * Сформировать список пунктов.
     * @return NavItem[]
     */
    protected function getItems(): array
    {
        $items = [];

        if ($this->prevNext) {
            $items[] = $this->prev();
        }

        $items = array_merge($items, $this->getNumbers());

        if ($this->prevNext) {
            $items[] = $this->next();
        }

        return $items;
    }

    /**
     * Номера страниц.
     * @return NavItem[]
     */
    protected function getNumbers(): array
    {
        $current = $this->pagination->page; // Номер текущей страницы
        $total = $this->pagination->amount; // Кол-во страниц
        $from = $current - 2;
        $to = $current + 2;

        $result = [];
        for ($i = $from; $i <= $to && $i <= $total; $i++) {
            $disabled = $i == $current;

            $result[] = new NavItem([
                'label' => $i,
                'url' => $disabled ? '#' : $this->getUrl($i),
                'visible' => 0 < $i,
                'active' => $disabled,
            ]);
        }
        return $result;
    }

    /**
     * Предыдущая страница.
     * @return NavItem
     */
    protected function prev(): NavItem
    {
        $page = $this->pagination->page - 1;
        $disabled = $page <= 0;

        return new NavItem([
            'label' => '&laquo;',
            'url' => $disabled ? '#' : $this->getUrl($page),
            'active' => false,
            'htmlAttributes' => ['class' => $disabled ? $this->disabledClass : false],
        ]);
    }

    /**
     * Следующая страница.
     * @return NavItem
     */
    protected function next(): NavItem
    {
        $page = $this->pagination->page + 1;
        $disabled = $this->pagination->amount < $page;

        return new NavItem([
            'label' => '&raquo;',
            'url' => $disabled ? '#' : $this->getUrl($page),
            'active' => false,
            'htmlAttributes' => ['class' => $disabled ? $this->disabledClass : false],
        ]);
    }

    /**
     * Сгенерировать адрес страницы.
     * @param int $page - номер страницы
     * @return string|null
     */
    private function getUrl(int $page): ?string
    {
        if ($page == 1) {
            $page = null;
        }

        return Url::current([$this->parameter => $page]);
    }
}
