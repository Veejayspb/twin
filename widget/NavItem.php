<?php

namespace twin\widget;

use twin\controller\Controller;
use twin\helper\Html;
use twin\helper\Tag;
use twin\route\RouteManager;
use twin\Twin;

class NavItem extends Widget
{
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
    public $htmlAttributes = [];

    /**
     * HTML-атрибуты ссылки <a>...</a>.
     * @var array
     */
    public $linkAttributes = [];

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return $this->visible ? $this->getItem() : '';
    }

    /**
     * Является ли пункт активным.
     * @return bool
     */
    public function isActive(): bool
    {
        // Если статус указан явно
        if (is_bool($this->active)) {
            return $this->active;
        }

        if (!is_string($this->url)) {
            return false;
        }

        $routeManager = Twin::app()->findComponent(RouteManager::class); /* @var RouteManager $routeManager */

        if (!$routeManager) {
            return false;
        }

        $route = $routeManager->parseUrl($this->url);

        if ($route === false) {
            return false;
        }

        $current = $routeManager->getCurrentRoute();

        if (!$current) {
            return false;
        }

        return
            $current->module == $route->module &&
            $current->controller == $route->controller &&
            $current->action == $route->action;
    }

    /**
     * Сформировать пункт меню.
     * @return string
     */
    private function getItem(): string
    {
        $content = $this->url === null ? $this->label : $this->getLink();

        return new Tag(
            'li',
            $this->htmlAttributes,
            $content . $this->extra
        );
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
