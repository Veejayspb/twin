<?php

namespace twin\helper;

class Html
{
    const BR = '<br>';
    const SPACE = ' ';
    const TAB = "\t";

    /**
     * Плейсхолдер уникального числа.
     * @see uniqueStr()
     */
    const UNIQUE_PLACEHOLDER = '{num}';

    /**
     * Счетчик уникального числового значения.
     * @var int
     * @see uniqueStr()
     */
    protected static int $uniqueNumber = 0;

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
        $tag = new Tag($name, $htmlAttributes);
        return $tag->open();
    }

    /**
     * Закрыть тег.
     * @param string $name - название тега
     * @return string
     */
    public static function tagClose(string $name): string
    {
        $tag = new Tag($name);
        return $tag->close();
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
     * Изображение.
     * @param string $src - адрес
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function img(string $src, array $htmlAttributes = []): string
    {
        $htmlAttributes['src'] = $src;
        return static::tag('img', $htmlAttributes);
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
        $htmlAttributes['type'] ??= 'submit';
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
        return static::input('text', $value, $htmlAttributes);
    }

    /**
     * Поле для электронной почты.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputEmail(string $value, array $htmlAttributes = []): string
    {
        return static::input('email', $value, $htmlAttributes);
    }

    /**
     * Поле для пароля.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputPassword(string $value, array $htmlAttributes = []): string
    {
        return static::input('password', $value, $htmlAttributes);
    }

    /**
     * Скрытое поле.
     * @param string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputHidden(string $value, array $htmlAttributes = []): string
    {
        return static::input('hidden', $value, $htmlAttributes);
    }

    /**
     * Поле для загрузки файла.
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function inputFile(array $htmlAttributes = []): string
    {
        return static::input('file', null, $htmlAttributes);
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
     * @param array|string $value - значение / массив значений (если есть атрибут multiple)
     * @param array $options - список опций
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function select(array|string $value, array $options, array $htmlAttributes = []): string
    {
        $value = (array)$value;
        $result = static::tagOpen('select', $htmlAttributes);

        foreach ($options as $key => $val) {
            $result.= static::tag('option', [
                'value' => $key,
                'selected' => in_array($key, $value),
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

        if (!array_key_exists('name', $htmlAttributes)) {
            $htmlAttributes['name'] = static::uniqueStr('name-' . static::UNIQUE_PLACEHOLDER);
        }

        foreach ($options as $key => $val) {
            $htmlAttributes['value'] = $key;
            $htmlAttributes['checked'] = $value == $key;

            $content = static::tagOpen('input', $htmlAttributes);
            $content.= static::SPACE . $val;

            $result[] = static::label($content);
        }

        return implode($separator, $result);
    }

    /**
     * Чекбокс.
     * @param int|string $value - значение
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function checkbox(int|string $value = 1, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] ??= 'checkbox';
        $htmlAttributes['value'] = $value;
        return static::tagOpen('input', $htmlAttributes);
    }

    /**
     * Генератор уникальной строки для использования в кач-ве значений HTML-атрибутов.
     * @param string $pattern - паттерн строки вида: str-{num}
     * @return string
     */
    public static function uniqueStr(string $pattern = 'str-' . self::UNIQUE_PLACEHOLDER): string
    {
        if (!strstr($pattern, static::UNIQUE_PLACEHOLDER)) {
            $pattern.= '-' . static::UNIQUE_PLACEHOLDER;
        }

        return str_replace(static::UNIQUE_PLACEHOLDER, ++static::$uniqueNumber, $pattern);
    }

    /**
     * Добавить CSS-класс (если он не сущ-ет) в массив HTML-атрибутов.
     * @param array $htmlAttributes - HTML-атрибуты
     * @param string $class - название класса
     * @return void
     */
    public static function addCssClass(array &$htmlAttributes, string $class)
    {
        $class = trim($class);

        if ($class == '') {
            return;
        }

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

    /**
     * Поле для ввода.
     * @param string $type
     * @param string|null $value
     * @param array $htmlAttributes
     * @return string
     */
    protected static function input(string $type, string|null $value = null, array $htmlAttributes = []): string
    {
        $htmlAttributes['type'] ??= $type;
        $htmlAttributes['value'] ??= $value;
        return static::tagOpen('input', $htmlAttributes);
    }
}
