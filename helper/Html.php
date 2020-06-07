<?php

namespace twin\helper;

class Html
{
    const BR = '<br>';
    const SPACE = ' ';
    const TAB = "\t";

    /**
     * Экранирование спецсимволов.
     * @param string $text - исходный текст
     * @return string
     */
    public static function encode(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }

    /**
     * Открыть тег.
     * @param string $name - название тега
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function tagOpen(string $name, array $htmlAttributes = []): string
    {
        return (new Tag($name, $htmlAttributes))->open();
    }

    /**
     * Закрыть тег.
     * @param string $name - название тега
     * @return string
     */
    public static function tagClose(string $name): string
    {
        return (new Tag($name))->close();
    }

    /**
     * Парный тег.
     * @param string $name - название тега
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $content - содержимое тега
     * @return string
     */
    public static function tag(string $name, array $htmlAttributes = [], $content = ''): string
    {
        return (string)new Tag($name, $htmlAttributes, (string)$content);
    }

    /**
     * Ссылка.
     * @param string $url - адрес
     * @param string $content - содержимое ссылки
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function a(string $url, string $content = '', array $htmlAttributes = []): string
    {
        $htmlAttributes['href'] = $url;
        return static::tag('a', $htmlAttributes, $content);
    }

    /**
     * Тег LABEL.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function label(string $value, array $htmlAttributes = []): string
    {
        return static::tag('label', $htmlAttributes, $value);
    }

    /**
     * Кнопка отправки формы.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function submit(string $value, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'submit';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Текстовое поле.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputText($value, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'text';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Поле для пароля.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputPassword($value, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'password';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Скрытое поле.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputHidden($value, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'hidden';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Текстовая область.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function textArea($value, array $htmlAttributes = []): string
    {
        return static::tag('textarea', $htmlAttributes, $value);
    }

    /**
     * Выпадающий список.
     * @param string $value - значение
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function select($value, array $options, array $htmlAttributes = []): string
    {
        $result = static::tagOpen('select', $htmlAttributes);
        foreach ($options as $key => $val) {
            $result.= static::tag('option', [
                'value' => $key,
                'selected' => $value == $key,
            ], $val);
        }
        $result.= static::tagClose('select');
        return $result;
    }

    /**
     * Радиокнопки.
     * @param string $value - значение
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $separator - разделитель
     * @return string
     */
    public static function radio($value, array $options, array $htmlAttributes = [], string $separator = PHP_EOL): string
    {
        $result = [];
        $htmlAttributes['type'] = 'radio';
        foreach ($options as $key => $val) {
            $htmlAttributes['value'] = $key;
            $htmlAttributes['checked'] = $value == $key ? true : false;
            $content = static::tagOpen('input', $htmlAttributes);
            $content.= static::SPACE . $val;
            $result[] = static::label($content);
        }
        return implode($separator, $result);
    }

    /**
     * Чекбокс.
     * @param string|int $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function checkbox($value = 1, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'checkbox';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Добавить CSS-класс (если он не сущ-ет) в массив HTML-атрибутов.
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $class - название класса
     * @return void
     */
    public static function addCssClass(array &$htmlAttributes, string $class)
    {
        if (array_key_exists('class', $htmlAttributes)) {
            $items = explode(static::SPACE, $htmlAttributes['class']);
        } else {
            $items = [];
        }
        if (!in_array($class, $items)) {
            $items[] = $class;
            $htmlAttributes['class'] = implode(static::SPACE, $items);
        }
    }
}
