<?php

namespace twin\widget;

use twin\common\Exception;
use twin\helper\Html;
use twin\model\Form;
use ReflectionClass;

class FormWidget extends Widget
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
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function label(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $label = $form->getLabel($attribute);
        $htmlAttributes['for'] = $htmlAttributes['for'] ?? $this->getAttributeId($form, $attribute);
        return Html::label($label, $htmlAttributes);
    }

    /**
     * Вывод ошибки.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function error(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $error = $form->getError($attribute);
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
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputText(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::inputText($form->$attribute, $htmlAttributes);
    }

    /**
     * Поле для электронной почты.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputEmail(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::inputEmail($form->$attribute, $htmlAttributes);
    }

    /**
     * Поле для пароля.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputPassword(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::inputPassword($form->$attribute, $htmlAttributes);
    }

    /**
     * Скрытое поле.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputHidden(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::inputHidden($form->$attribute, $htmlAttributes);
    }

    /**
     * Поле для загрузки файла.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function inputFile(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $this->htmlAttributes['enctype'] = 'multipart/form-data';
        $name = $this->getAttributeName($form, $attribute);
        if (!empty($htmlAttributes['multiple'])) {
            $name.= '[]';
        }
        $htmlAttributes['name'] = $name;
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::inputFile($htmlAttributes);
    }

    /**
     * Текстовая область.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function textArea(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::textArea($form->$attribute, $htmlAttributes);
    }

    /**
     * Выпадающий список.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function select(Form $form, string $attribute, array $options = [], array $htmlAttributes = []): string
    {
        $result = '';
        $name = $this->getAttributeName($form, $attribute);
        if (isset($htmlAttributes['multiple'])) {
            $result.= Html::inputHidden(true, ['name' => $name]);
            $name.= '[]';
        }
        $htmlAttributes['name'] = $name;
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        $result.= Html::select($form->$attribute, $options, $htmlAttributes);
        return $result;
    }

    /**
     * Радиокнопки.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $separator - разделитель
     * @return string
     */
    public function radio(Form $form, string $attribute, array $options = [], array $htmlAttributes = [], string $separator = PHP_EOL): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        return Html::radio($form->$attribute, $options, $htmlAttributes, $separator);
    }

    /**
     * Чекбокс.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public function checkbox(Form $form, string $attribute, array $htmlAttributes = []): string
    {
        $htmlAttributes['name'] = $this->getAttributeName($form, $attribute);
        $result = Html::inputHidden(0, $htmlAttributes);
        $htmlAttributes['id'] = $htmlAttributes['id'] ?? $this->getAttributeId($form, $attribute);
        $htmlAttributes['checked'] = (bool)$form->$attribute;
        $result.= Html::checkbox(1, $htmlAttributes);
        return $result;
    }

    /**
     * Вызов виджета.
     * @param string $class - класс виджета
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @param array $htmlAttributes - свойства
     * @return string
     * @throws Exception
     */
    public function widget(string $class, Form $form, string $attribute, array $htmlAttributes = []): string
    {
        if (!is_subclass_of($class, FormField::class)) {
            throw new Exception(500, "$class must extends " . FormField::class);
        }

        $properties = [
            'model' => $form,
            'attribute' => $attribute,
            'htmlAttributes' => $htmlAttributes,
            'parent' => $this,
        ];

        $widget = new $class($properties); /* @var FormField $widget */
        return $widget->run();
    }

    /**
     * Сгенерировать название поля на основе формы и атрибута.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getAttributeName(Form $form, string $attribute): string
    {
        $reflection = new ReflectionClass($form);
        $className = $reflection->getShortName();
        return "{$className}[$attribute]";
    }

    /**
     * Сгенерировать ID поля на основе формы и атрибута.
     * @param Form $form - форма
     * @param string $attribute - название атрибута
     * @return string
     */
    public function getAttributeId(Form $form, string $attribute): string
    {
        $reflection = new ReflectionClass($form);
        $className = $reflection->getShortName();
        return mb_strtolower("$className-$attribute", 'utf-8');
    }

    /**
     * Открытие формы.
     * @return string
     */
    protected function start(): string
    {
        $attributes = $this->htmlAttributes;
        $attributes['action'] = $this->action;
        $attributes['method'] = $this->method;
        return Html::tagOpen('form', $attributes);
    }
}
