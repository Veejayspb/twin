<?php

namespace twin\widget;

use twin\helper\Html;
use twin\model\Model;
use ReflectionClass;

class Form extends Widget
{
    /**
     * Адрес отправки формы по-умолчанию.
     */
    const ACTION = '';

    /**
     * Метод отправки формы по-умолчанию.
     */
    const METHOD = 'post';

    /**
     * Адрес отправки формы.
     * @var string
     */
    protected $action = self::ACTION;

    /**
     * Метод отправки формы.
     * @var string
     */
    protected $method = self::METHOD;

    /**
     * HTML-атрибуты.
     * @var array
     */
    protected $htmlAttributes = [];

    public function __toString()
    {
        return $this->start();
    }

    /**
     * Открытие формы.
     * @return string
     */
    public function start()
    {
        $attributes = $this->htmlAttributes;
        $attributes['action'] = $this->action;
        $attributes['method'] = $this->method;
        return Html::tagOpen('form', $attributes);
    }

    /**
     * Закрытие формы.
     * @return string
     */
    public function run(): string
    {
        return Html::tagClose('form');
    }

    /**
     * Тег LABEL.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     * @todo: for="field-id"
     */
    public function label(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $label = $model->getLabel($attribute);
        return Html::label($label, $htmlAttributes);
    }

    /**
     * Вывод ошибки.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function error(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $error = $model->getError($attribute);
        return $error ? Html::tag('div', $htmlAttributes, $error) : '';
    }

    /**
     * Кнопка отправки формы.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function submit(string $value, array $htmlAttributes = []): string
    {
        return Html::submit($value, $htmlAttributes);
    }

    /**
     * Текстовое поле.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputText(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputText($model->$attribute, $htmlAttributes);
    }

    /**
     * Поле для пароля.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputPassword(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputPassword($model->$attribute, $htmlAttributes);
    }

    /**
     * Скрытое поле.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputHidden(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputHidden($model->$attribute, $htmlAttributes);
    }

    /**
     * Текстовая область.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function textArea(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::textArea($model->$attribute, $htmlAttributes);
    }

    /**
     * Выпадающий список.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function select(Model $model, string $attribute, array $options = [], array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::select($model->$attribute, $options, $htmlAttributes);
    }

    /**
     * Радиокнопки.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $separator - разделитель
     * @return string
     */
    public function radio(Model $model, string $attribute, array $options = [], array $htmlAttributes = [], string $separator = PHP_EOL): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::radio($model->$attribute, $options, $htmlAttributes, $separator);
    }

    /**
     * Чекбокс.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function checkbox(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($model, $attribute);
        $result = Html::inputHidden(0, $htmlAttributes);
        $result.= Html::checkbox(1, $htmlAttributes);
        return $result;
    }

    /**
     * Сформировать название поля формы на основе модели и атрибута.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @return string
     */
    protected function getAttributeName(Model $model, string $attribute): string
    {
        $reflection = new ReflectionClass($model);
        $className = $reflection->getShortName();
        return "{$className}[$attribute]";
    }
}
