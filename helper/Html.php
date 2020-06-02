<?php

namespace twin\helper;

class Html
{
    const BR = '<br>';
    const SPACE = ' ';

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
    public static function tag(string $name, array $htmlAttributes = [], string $content = ''): string
    {
        return (string)new Tag($name, $htmlAttributes, $content);
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
    public static function inputText(string $value, array $htmlAttributes = []): string
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
    public static function inputPassword(string $value, array $htmlAttributes = []): string
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
    public static function inputHidden(string $value, array $htmlAttributes = []): string
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
    public static function textArea(string $value, array $htmlAttributes = []): string
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
    public static function select(string $value, array $options, array $htmlAttributes = []): string
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
    public static function radio(string $value, array $options, array $htmlAttributes = [], string $separator = PHP_EOL): string
    {
        $result = [];
        $htmlAttributes['type'] = 'radio';
        foreach ($options as $key => $val) {
            $htmlAttributes['value'] = $key;
            $htmlAttributes['checked'] = $value == $key ? true : false;
            $content = static::tagOpen('input', $htmlAttributes);
            $content.= self::SPACE . $val;
            $result[] = static::label($content);
        }
        return implode($separator, $result);
    }

    /**
     * Чекбокс.
     * @param string $value - значение
     * @param string $label - ярлык
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function checkbox(string $value, string $label, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] = 'checkbox';
        $htmlAttributes['value'] = $value;
        $input = static::tagOpen('input', $htmlAttributes);
        return static::label($input . self::SPACE . $label);
    }
}
