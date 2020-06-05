<?php

namespace twin\widget;

use twin\common\SetPropertiesTrait;
use twin\helper\Html;
use twin\helper\Request;
use twin\helper\Tag;

class Nav extends Widget
{
    /**
     * Пункты меню.
     * @var array
     * @see NavItem
     */
    public $items = [];

    /**
     * HTML-атрибуты главного тега UL.
     * @var array
     */
    public $attributes = [];

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
        $items = [];
        foreach ($this->items as $options) {
            $items[] = $this->getItem($options);
        }
        return Html::tag('ul', $this->attributes, implode(PHP_EOL, $items));
    }

    /**
     * Создать пункт меню с указанными настроками.
     * @param array $options - настройки
     * @return NavItem
     */
    protected function getItem(array $options): NavItem
    {
        $item = new NavItem($options);
        if ($item->isActive()) {
            Html::addCssClass($item->attributes, $this->activeClass);
        }
        return $item;
    }
}

final class NavItem
{
    use SetPropertiesTrait;

    /**
     * Ярлык.
     * @var string
     */
    public $label = '';

    /**
     * Адрес ссылки.
     * Если не указано, ссылка не формируется.
     * @var string|null
     */
    public $url;

    /**
     * Видимость пункта.
     * @var bool
     */
    public $visible = true;

    /**
     * Активен ли пункт.
     * Если не указано, то выставляется автоматически.
     * @var bool|null
     */
    public $active;

    /**
     * Дополнительные данные, вставляемые в конце пункта.
     * Используется для создания вложенных пунктов.
     * @var string
     */
    public $extra = '';

    /**
     * HTML-атрибуты пункта меню <li>...</li>.
     * @var array
     */
    public $attributes = [];

    /**
     * HTML-атрибуты ссылки <a>...</a>.
     * @var array
     */
    public $linkAttributes = [];

    /**
     * @param array $options - настройки пункта меню
     */
    public function __construct(array $options)
    {
        $this->setProperties($options);
    }

    public function __toString()
    {
        return $this->visible ? $this->getItem() : '';
    }

    /**
     * Является ли пункт активным.
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->active === false) return false;
        if ($this->active === true) return true;
        return $this->url == Request::$url;
    }

    /**
     * Сформировать пункт меню.
     * @return string
     */
    private function getItem(): string
    {
        $link = $this->url === null ? $this->label : $this->getLink();
        return new Tag('li', $this->attributes, $link . $this->extra);
    }

    /**
     * Сформировать ссылку.
     * @return string
     */
    private function getLink(): string
    {
        return Html::a(
            $this->url,
            $this->label,
            $this->linkAttributes
        );
    }
}
