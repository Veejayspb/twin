<?php

namespace twin\widget;

use twin\common\Exception;
use twin\helper\Html;
use twin\helper\Pagination;
use twin\helper\Url;

class PaginationWidget extends Nav
{
    /**
     * CSS-класс неактивного пункта меню.
     * @var string
     */
    public $disabledClass = 'disabled';

    /**
     * Название GET-параметра со страницей.
     * @var string
     */
    public $parameter = 'page';

    /**
     * Отображать кнопки предыдущая/следующая страница.
     * @var bool
     */
    public $prevNext = true;

    /**
     * {@inheritdoc}
     */
    public $htmlAttributes = [
        'class' => 'pagination',
    ];

    /**
     * @var Pagination
     */
    protected $pagination;

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
        // Если страница всего одна, то не выводить пагинатор.
        if ($this->pagination->amount == 1) {
            return '';
        }

        $items = [];

        if ($this->prevNext) {
            $items[] = $this->prev();
        }

        $items = array_merge($items, $this->getNumbers());

        if ($this->prevNext) {
            $items[] = $this->next();
        }

        return Html::tag('ul', $this->htmlAttributes, implode(PHP_EOL, $items));
    }

    /**
     * Номера страниц.
     * @return array
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

            $result[] = $this->getItem([
                'label' => $i,
                'url' => $this->getUrl($i, $disabled),
                'visible' => 0 < $i,
                'active' => $current == $i,
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

        return $this->getItem([
            'label' => '&laquo;',
            'url' => $this->getUrl($page, $disabled),
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

        return $this->getItem([
            'label' => '&raquo;',
            'url' => $this->getUrl($page, $disabled),
            'active' => false,
            'htmlAttributes' => ['class' => $disabled ? $this->disabledClass : false],
        ]);
    }

    /**
     * Сгенерировать адрес страницы.
     * @param int $page - номер страницы
     * @param bool $disabled - сделать ссылку неактивной
     * @return string
     */
    private function getUrl(int $page, bool $disabled = false): string
    {
        if ($disabled) return '#';
        $page = $page == 1 ? null : $page;
        return Url::current([$this->parameter => $page]);
    }
}
