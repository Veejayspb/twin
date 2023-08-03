<?php

namespace twin\helper;

/**
 * Хелпер для формирования HTML-тегов.
 *
 * Class Tag
 */
class Tag
{
    /**
     * Одиночные теги.
     */
    const SINGLE_TAGS = [
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
     * Название тега по-умолчанию.
     */
    const DEFAULT_NAME = 'undefined';

    /**
     * Паттерн названия атрибута тега.
     */
    const ATTRIBUTE_NAME_PATTERN = '/^[a-z\-]+$/';
    
    /**
     * Название тега.
     * @var string
     */
    protected $name;

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
     * @param array $attributes - атрибуты тега
     * @param string $content - содержимое тега
     */
    public function __construct(string $name, array $attributes = [], string $content = '')
    {
        $this->setName($name);
        $this->setAttributes($attributes);
        $this->setContent($content);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = $this->open();

        if (!$this->isSingle()) {
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
        $attributes = $this->renderAttributes();

        if (!empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

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
     * Указать название тега.
     * @param string $value
     * @return void
     */
    public function setName(string $value): void
    {
        $this->name = $value ?: static::DEFAULT_NAME;
    }

    /**
     * Вернуть название тега.
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Указать значение атрибута тега.
     * Чтобы удалить атрибут, необходимо выставить его значение в NULL или FALSE.
     * @param mixed $name - название атрибута
     * @param mixed $value - значение атрибута
     * @return bool
     */
    public function setAttribute($name, $value): bool
    {
        if (!$this->isValidAttributeName($name) || !$this->isValidAttributeValue($value)) {
            return false;
        }

        $this->attributes[$name] = $value;

        if ($this->isEmptyAttributeValue($value)) {
            unset($this->attributes[$name]);
        }

        return true;
    }

    /**
     * Указать значения атрибутов тега.
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = [];

        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Вернуть значение атрибута тега.
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : null;
    }

    /**
     * Вернуть атрибуты тега.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Указать содержимое тега.
     * @param string $value
     * @return void
     */
    public function setContent(string $value): void
    {
        $this->content = $value;
    }

    /**
     * Вернуть содержимое тега.
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Является ли тег одиночным.
     * @return bool
     */
    public function isSingle(): bool
    {
        return in_array($this->name, static::SINGLE_TAGS);
    }

    /**
     * Является ли значение атрибута пустым.
     * @param mixed $value
     * @return bool
     */
    protected function isEmptyAttributeValue($value): bool
    {
        return in_array($value, [null, false], true);
    }

    /**
     * Является ли название атрибута корректным.
     * @param mixed $name
     * @return bool
     */
    protected function isValidAttributeName($name): bool
    {
        return
            is_string($name) &&
            preg_match(static::ATTRIBUTE_NAME_PATTERN, $name);
    }

    /**
     * Является ли значение атрибута корректным.
     * @param mixed $value
     * @return bool
     */
    protected function isValidAttributeValue($value): bool
    {
        $type = gettype($value);
        return in_array($type, ['string', 'integer', 'boolean', 'NULL']);
    }

    /**
     * Сформировать строку с атрибутами для тега.
     * @return string
     */
    protected function renderAttributes(): string
    {
        $result = [];

        foreach ($this->attributes as $key => $value) {
            $result[] = $this->renderAttribute($key, $value);
        }

        return implode(' ', $result);
    }

    /**
     * Сформировать строковый атрибут тега, имея ключ и значение.
     * @param string $key
     * @param true|string|array $value
     * @return string
     */
    private function renderAttribute(string $key, $value): string
    {
        if ($value === true) {
            return $key;
        }

        $value = htmlentities((string)$value, ENT_QUOTES);
        return "$key=\"$value\"";
    }
}
