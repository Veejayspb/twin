<?php

namespace twin\widget;

use twin\helper\Html;

abstract class ActiveWidget extends Widget
{
    /**
     * CSS-класс.
     */
    const CSS_CLASS = '';

    /**
     * Адрес отправки запроса.
     * @var string
     */
    public $url;

    /**
     * Фиксированные POST-параметры.
     * @var array
     */
    public $params = [];

    /**
     * Текущее состояние виджета.
     * @var bool
     */
    public $value;

    /**
     * HTML-атрибуты.
     * @var array
     */
    public $htmlAttributes = [];

    /**
     * Сформировать итоговый массив HTML-атрибутов.
     * @return array
     */
    protected function getHtmlAttributes(): array
    {
        $htmlAttributes = $this->htmlAttributes;
        $htmlAttributes['data-url'] = $this->url;
        $htmlAttributes['data-params'] = json_encode($this->params);
        Html::addCssClass($htmlAttributes, static::CSS_CLASS);
        return $htmlAttributes;
    }

    /**
     * Формирование ответа на запрос.
     * @param bool $value - новое значение
     * @return string
     */
    public static function response($value): string
    {
        header('Content-Type: application/json');
        return json_encode(['value' => $value]);
    }
}
