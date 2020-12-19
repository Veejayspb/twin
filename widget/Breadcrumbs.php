<?php

namespace twin\widget;

use twin\helper\Html;

class Breadcrumbs extends Widget
{
    /**
     * Тег контейнера.
     * @var string
     */
    public $tagContainer = 'ol';

    /**
     * Тег пункта.
     * @var string
     */
    public $tagItem = 'li';

    /**
     * Класс активного пункта.
     * @var string
     */
    public $activeClass = 'active';

    /**
     * HTML-атрибуты контейнера.
     * @var array
     */
    public $htmlAttributes = [
        'class' => 'breadcrumb',
    ];

    /**
     * Пункты.
     * @var array
     * Ключ - название пункта
     * Значение - url страницы
     */
    protected $items = [
        #'Главная' => '/',
    ];

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $content = parent::run();
        $items = $this->items;
        end($items);
        $lastKey = key($items);
        if ($lastKey) {
            $items[$lastKey] = null;
        }
        return $content . $this->renderContainer($items);
    }

    /**
     * Рендер контейнера.
     * @param array $items - пункты
     * @return string
     */
    private function renderContainer(array $items): string
    {
        $result = '';
        foreach ($items as $label => $url) {
            $result.= $this->renderItem($label, $url);
        }
        return Html::tag($this->tagContainer, $this->htmlAttributes, $result);
    }

    /**
     * Рендер пункта.
     * @param string $label - ярлык
     * @param string|null $url - адрес
     * @return string
     */
    private function renderItem(string $label, $url = null): string
    {
        return Html::tag($this->tagItem, [
            'class' => $url === null ? $this->activeClass : null,
        ], $this->a($label, $url));
    }

    /**
     * Рендер тега A.
     * @param string $label - ярлык
     * @param string|null $url - адрес
     * @return string
     */
    private function a(string $label, $url = null): string
    {
        if ($url === null) {
            return $label;
        } else {
            return Html::a($url, $label);
        }
    }
}
