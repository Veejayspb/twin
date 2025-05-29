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
    public string $url;

    /**
     * Фиксированные POST-параметры.
     * @var array
     */
    public array $params = [];

    /**
     * Текущее состояние виджета.
     * @var mixed
     */
    public mixed $value;

    /**
     * HTML-атрибуты.
     * @var array
     */
    public array $htmlAttributes = [];

    /**
     * Сформировать итоговый массив HTML-атрибутов.
     * @return array
     */
    protected function getHtmlAttributes(): array
    {
        $htmlAttributes = $this->htmlAttributes;
        $htmlAttributes['data-url'] = $this->url;
        $htmlAttributes['data-params'] = json_encode((object)$this->params);
        Html::addCssClass($htmlAttributes, static::CSS_CLASS);
        return $htmlAttributes;
    }

    /**
     * Формирование ответа на запрос.
     * @param mixed $value - новое значение
     * @return string
     */
    public static function response(mixed $value): string
    {
        header('Content-Type: application/json');
        return json_encode(['value' => $value]);
    }
}
