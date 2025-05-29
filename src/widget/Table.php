<?php

namespace twin\widget;

use twin\helper\Html;

class Table extends Widget
{
    /**
     * Массив данных для вывода.
     * @var array
     */
    public array $items = [];

    /**
     * Список столбцов.
     * ключ - название
     * значение - коллбэк функция, которая получает через параметр элемент массива $rows
     * @var array
     */
    public array $columns = [];

    /**
     * Сопоставление названий столбцов и заголовков.
     * @var array
     */
    public array $labels = [];

    /**
     * Ширина столбцов.
     * ключ - название
     * значение - значение ширины (150, 20%)
     * @var array
     */
    public array $width = [];

    /**
     * HTML-атрибуты таблицы.
     * @var array
     */
    public array $htmlAttributes = [];

    /**
     * Коллбэк-функция, определяющая атрибуты элементов TR.
     * function ($item, $index) {
     *     return [];
     * }
     * @var callable|null
     */
    public $trAttributes;

    /**
     * Коллбэк-функция, определяющая атрибуты элементов TD.
     * function ($item, $name) {
     *     return [];
     * }
     * @var callable|null
     */
    public $tdAttributes;

    /**
     * Коллбэк-функция, определяющая атрибуты элементов TH.
     * function ($name) {
     *     return [];
     * }
     * @var callable|null
     */
    public $thAttributes;

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::tag(
            'table',
            $this->htmlAttributes,
            $this->colgroup() . $this->thead() . $this->tbody()
        );
    }

    /**
     * Настройки столбцов.
     * @return string
     */
    protected function colgroup(): string
    {
        $result = [];

        foreach ($this->columns as $name => $callback) {
            $width = $this->width[$name] ?? null;
            $result[] = Html::tag('col', ['width' => $width]);
        }

        $result = implode('', $result);
        return Html::tag('colgroup', [], $result);
    }

    /**
     * Заголовок таблицы.
     * @return string
     */
    protected function thead(): string
    {
        $result = [];

        foreach ($this->columns as $name => $callback) {
            $thAttributes = is_callable($this->thAttributes) ? call_user_func_array($this->thAttributes, [$name]) : [];
            $label = is_int($name) ? '' : $this->getLabel($name);
            $result[] = Html::tag('th', $thAttributes, $label);
        }

        $result = implode('', $result);
        $result = Html::tag('tr', [], $result);
        return Html::tag('thead', [], $result);
    }

    /**
     * Тело таблицы.
     * @return string
     */
    protected function tbody(): string
    {
        $result = [];

        foreach (array_values($this->items) as $i => $item) {
            $result[] = $this->row($item, $i);
        }

        $result = implode('', $result);
        return Html::tag('tbody', [], $result);
    }

    /**
     * Ряд таблицы.
     * @param mixed $item
     * @param int $index
     * @return string
     */
    protected function row(mixed $item, int $index): string
    {
        $result = [];
        $trAttributes = is_callable($this->trAttributes) ? call_user_func_array($this->trAttributes, [$item, $index]) : [];

        foreach ($this->columns as $name => $callback) {
            $tdAttributes = is_callable($this->tdAttributes) ? call_user_func_array($this->tdAttributes, [$item, $name]) : [];
            $result[] = Html::tag('td', (array)$tdAttributes, $callback($item));
        }

        return Html::tag('tr', (array)$trAttributes, implode('', $result));
    }

    /**
     * Вернуть заголовок по названию столбца.
     * @param string $name
     * @return string
     */
    private function getLabel(string $name): string
    {
        return $this->labels[$name] ?? $name;
    }
}
