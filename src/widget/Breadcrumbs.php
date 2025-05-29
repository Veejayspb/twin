<?php

namespace twin\widget;

use twin\helper\Html;

class Breadcrumbs extends Widget
{
    /**
     * Тег контейнера.
     * @var string
     */
    public string $tagContainer = 'ol';

    /**
     * Тег пункта.
     * @var string
     */
    public string $tagItem = 'li';

    /**
     * Класс активного пункта.
     * @var string
     */
    public string $activeClass = 'active';

    /**
     * HTML-атрибуты контейнера.
     * @var array
     */
    public array $htmlAttributes = [
        'class' => 'breadcrumb',
    ];

    /**
     * Пункты.
     * @var array
     * Ключ - название пункта
     * Значение - url страницы
     */
    public array $items = [
        #'Главная' => '/',
    ];

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        $items = $this->items;
        end($items);
        $lastKey = key($items);
        if ($lastKey) {
            $items[$lastKey] = null;
        }
        return $this->renderContainer($items);
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
    private function renderItem(string $label, ?string $url = null): string
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
    private function a(string $label, ?string $url = null): string
    {
        if ($url === null) {
            return $label;
        } else {
            return Html::a($url, $label);
        }
    }
}
