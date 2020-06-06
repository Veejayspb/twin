<?php

namespace twin\helper;

/**
 * Хелпер для формирования HTML-тегов.
 * Атрибуты тега могут задаваться и считываться напрямую:
 * $tag->attr = 'value';
 * $value = $tag->attr;
 * Если атрибут не сущ-ет, то вернется NULL.
 * Чтобы удалить атрибут, необходимо выставить его значение в NULL или FALSE.
 * В кач-ве значения атрибута может выступать массив. Результатом будет склейка всех значений через пробел.
 *
 * Class Tag
 * @package twin\helper
 */
class Tag
{
    /**
     * Одиночные теги.
     * @var array
     */
    public static $singleTags = [
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    ];
    
    /**
     * Название тега.
     * @var string
     */
    protected $name;

    /**
     * Является ли тег одиночным.
     * @var bool
     */
    protected $single = false;

    /**
     * Содержимое тега.
     * @var string
     */
    protected $content = '';

    /**
     * Атрибуты тега.
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string $name - название тега
     * @param array $htmlAttributes - атрибуты тега
     * @param string $content - содержимое тега
     * @param bool|null $single - является ли тег одиночным (если NULL, то определяется автоматически)
     */
    public function __construct(string $name, array $htmlAttributes = [], string $content = '', bool $single = null)
    {
        $this->name = $name;
        $this->attributes = $htmlAttributes;
        $this->content = $content;
        $this->single = $single === null ? in_array($name, static::$singleTags) : $single;
    }

    /**
     * @param string $name - название атрибута
     * @param string|array|null $value - значение
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
        if ($value === false || $value === null) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * @param string $name - название атрибута
     * @return mixed|null
     */
    public function __get($name)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = $this->open();
        if (!$this->single) {
            $result.= $this->content;
            $result.= $this->close();
        }
        return $result;
    }

    /**
     * Открывающий тег.
     * @return string
     */
    public function open(): string
    {
        $attributes = static::renderAttributes();
        return "<$this->name$attributes>";
    }

    /**
     * Закрывающий тег.
     * @return string
     */
    public function close(): string
    {
        return "</$this->name>";
    }

    /**
     * Сформировать строку с атрибутами для тега.
     * @return string
     */
    protected function renderAttributes(): string
    {
        $result = '';
        foreach ($this->attributes as $key => $value) {
            if ($value === true) {
                $result.= " $key";
            } elseif ($value !== false && $value !== null) {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                $result.= " $key=\"$value\"";
            }
        }
        return $result;
    }
}
