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
    protected $attributes = [];

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
        $attributes = $this->attributes;
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
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function label(Model $model, string $attribute, array $attributes = []): string
    {
        $label = $model->getLabel($attribute);
        return Html::label($label, $attributes);
    }

    /**
     * Вывод ошибки.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function error(Model $model, string $attribute, array $attributes = []): string
    {
        $error = $model->getError($attribute);
        return $error ? Html::tag('div', $attributes, $error) : '';
    }

    /**
     * Кнопка отправки формы.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function submit(string $value, array $attributes = []): string
    {
        return Html::submit($value, $attributes);
    }

    /**
     * Текстовое поле.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function inputText(Model $model, string $attribute, array $attributes = []): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputText($model->$attribute, $attributes);
    }

    /**
     * Поле для пароля.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function inputPassword(Model $model, string $attribute, array $attributes = []): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputPassword($model->$attribute, $attributes);
    }

    /**
     * Скрытое поле.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function inputHidden(Model $model, string $attribute, array $attributes = []): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::inputHidden($model->$attribute, $attributes);
    }

    /**
     * Выпадающий список.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function select(Model $model, string $attribute, array $options = [], array $attributes = []): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::select($model->$attribute, $options, $attributes);
    }

    /**
     * Радиокнопки.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $attributes - HTML-атрибуты
     * @param string $separator - разделитель
     * @return string
     */
    public function radio(Model $model, string $attribute, array $options = [], array $attributes = [], string $separator = PHP_EOL): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        return Html::radio($model->$attribute, $options, $attributes, $separator);
    }

    /**
     * Чекбокс.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public function checkbox(Model $model, string $attribute, array $attributes = []): string
    {
        $attributes['name'] = $this->getAttributeName($model, $attribute);
        $label = $model->getLabel($attribute);
        $result = Html::inputHidden(0, $attributes);
        $result.= Html::checkbox(1, $label, $attributes);
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
