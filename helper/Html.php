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
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function tagOpen(string $name, array $attributes = []): string
    {
        return (new Tag($name, $attributes))->open();
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
     * @param array $attributes - HTML-атрибуты
     * @param string $content - содержимое тега
     * @return string
     */
    public static function tag(string $name, array $attributes = [], string $content = ''): string
    {
        return (string)new Tag($name, $attributes, $content);
    }

    /**
     * Ссылка.
     * @param string $url - адрес
     * @param string $content - содержимое ссылки
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function a(string $url, string $content = '', array $attributes = []): string
    {
        $attributes['href'] = $url;
        return static::tag('a', $attributes, $content);
    }

    /**
     * Тег LABEL.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function label(string $value, array $attributes = []): string
    {
        return static::tag('label', $attributes, $value);
    }

    /**
     * Кнопка отправки формы.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function submit(string $value, array $attributes = []): string
    {
        $attributes['type'] = 'submit';
        $attributes['value'] = $value;
        return static::tagOpen('input', $attributes);
    }

    /**
     * Текстовое поле.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function inputText(string $value, array $attributes = []): string
    {
        $attributes['type'] = 'text';
        $attributes['value'] = $value;
        return static::tagOpen('input', $attributes);
    }

    /**
     * Поле для пароля.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function inputPassword(string $value, array $attributes = []): string
    {
        $attributes['type'] = 'password';
        $attributes['value'] = $value;
        return static::tagOpen('input', $attributes);
    }

    /**
     * Скрытое поле.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function inputHidden(string $value, array $attributes = []): string
    {
        $attributes['type'] = 'hidden';
        $attributes['value'] = $value;
        return static::tagOpen('input', $attributes);
    }

    /**
     * Текстовая область.
     * @param string $value - значение
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function textArea(string $value, array $attributes = []): string
    {
        return static::tag('textarea', $attributes, $value);
    }

    /**
     * Выпадающий список.
     * @param string $value - значение
     * @param array $options - список опций
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function select(string $value, array $options, array $attributes = []): string
    {
        $result = static::tagOpen('select', $attributes);
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
     * @param array $attributes - HTML-атрибуты
     * @param string $separator - разделитель
     * @return string
     */
    public static function radio(string $value, array $options, array $attributes = [], string $separator = PHP_EOL): string
    {
        $result = [];
        $attributes['type'] = 'radio';
        foreach ($options as $key => $val) {
            $attributes['value'] = $key;
            $attributes['checked'] = $value == $key ? true : false;
            $content = static::tagOpen('input', $attributes);
            $content.= static::SPACE . $val;
            $result[] = static::label($content);
        }
        return implode($separator, $result);
    }

    /**
     * Чекбокс.
     * @param string $value - значение
     * @param string $label - ярлык
     * @param array $attributes - HTML-атрибуты
     * @return string
     */
    public static function checkbox(string $value, string $label, array $attributes = []): string
    {
        $attributes['type'] = 'checkbox';
        $attributes['value'] = $value;
        $input = static::tagOpen('input', $attributes);
        return static::label($input . static::SPACE . $label);
    }

    /**
     * Добавить CSS-класс (если он не сущ-ет) в массив HTML-атрибутов.
     * @param array $attributes - HTML-атрибуты
     * @param string $class - название класса
     * @return void
     */
    public static function addCssClass(array &$attributes, string $class)
    {
        if (array_key_exists('class', $attributes)) {
            $items = explode(static::SPACE, $attributes['class']);
        } else {
            $items = [];
        }
        if (!in_array($class, $items)) {
            $items[] = $class;
            $attributes['class'] = implode(static::SPACE, $items);
        }
    }
}
