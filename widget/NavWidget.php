<?php

namespace twin\widget;

use twin\helper\Html;

class NavWidget extends Widget
{
    /**
     * Пункты меню.
     * @var NavItem[]
     */
    public $items = [];

    /**
     * HTML-атрибуты главного тега UL.
     * @var array
     */
    public $htmlAttributes = [];

    /**
     * CSS-класс активного пункта меню.
     * @var string
     */
    public $activeClass = 'active';

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        array_map(function (NavItem $item) {
            if ($item->isActive()) {
                Html::addCssClass($item->htmlAttributes, $this->activeClass);
            }
        }, $this->items);

        return Html::tag(
            'ul',
            $this->htmlAttributes,
            implode(PHP_EOL, $this->items)
        );
    }
}
