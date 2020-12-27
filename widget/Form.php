<?php

namespace twin\widget;

use twin\common\Exception;
use twin\helper\Html;
use twin\model\Model;
use ReflectionClass;

class Form extends Widget
{
    /**
     * Адрес отправки формы.
     * @var string
     */
    public $action = '';

    /**
     * Метод отправки формы.
     * @var string
     */
    public $method = 'post';

    /**
     * HTML-атрибуты.
     * @var array
     */
    public $htmlAttributes = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        ob_start();
    }

    /**
     * Закрытие формы.
     * @return string
     */
    public function run(): string
    {
        $result = $this->start();
        $result.= ob_get_clean();
        $result.= Html::tagClose('form');
        return $result;
    }

    /**
     * Тег LABEL.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function label(Model $model, string $attribute, array $htmlAttributes = []): string
    {
        $label = $model->getLabel($attribute);
        $htmlAttributes['for'] = $htmlAttributes['for'] ?? $this->getAttributeId($model, $attribute);
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
        return Html::inputHidden($model->$attribute, $htmlAttributes);
    }

    /**
     * Поле для загрузки файла.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputFile(Model $model, string $attribute, array $htmlAttributes = [])
    {
        $this->htmlAttributes['enctype'] = 'multipart/form-data';
        $name = $this->getAttributeName($model, $attribute);
        if (!empty($htmlAttributes['multiple'])) {
            $name.= '[]';
        }
        $htmlAttributes['name'] = $name;
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
        return Html::inputFile($htmlAttributes);
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
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
        $result = '';
        $name = $this->getAttributeName($model, $attribute);
        if (isset($htmlAttributes['multiple'])) {
            $result.= Html::inputHidden(true, ['name' => $name]);
            $name.= '[]';
        }
        $htmlAttributes['name'] = $name;
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
        $result.= Html::select($model->$attribute, $options, $htmlAttributes);
        return $result;
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
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
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($model, $attribute);
        $htmlAttributes['checked'] = (bool)$model->$attribute;
        $result.= Html::checkbox(1, $htmlAttributes);
        return $result;
    }

    /**
     * Вызов виджета.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @param string $class - класс виджета
     * @param array $properties - свойства
     * @return string
     * @throws Exception
     */
    public function widget(Model $model, string $attribute, string $class, array $properties = []): string
    {
        if (!is_subclass_of($class, FormField::class)) {
            throw new Exception(500, "$class must extends " . FormField::class);
        }

        $properties = [
            'model' => $model,
            'attribute' => $attribute,
            'parent' => $this,
        ] + $properties;

        $widget = new $class($properties); /* @var FormField $widget */
        return $widget->run();
    }

    /**
     * Сгенерировать название поля формы на основе модели и атрибута.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getAttributeName(Model $model, string $attribute): string
    {
        $reflection = new ReflectionClass($model);
        $className = $reflection->getShortName();
        return "{$className}[$attribute]";
    }

    /**
     * Сгенерировать ID поля формы на основе модели и атрибута.
     * @param Model $model - модель
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getAttributeId(Model $model, string $attribute): string
    {
        $reflection = new ReflectionClass($model);
        $className = $reflection->getShortName();
        return mb_strtolower("$className-$attribute", 'utf-8');
    }

    /**
     * Открытие формы.
     * @return string
     */
    protected function start()
    {
        $attributes = $this->htmlAttributes;
        $attributes['action'] = $this->action;
        $attributes['method'] = $this->method;
        return Html::tagOpen('form', $attributes);
    }
}
