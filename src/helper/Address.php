<?php

namespace twin\helper;

/**
 * Class Address
 *
 * @property bool $ssl
 * @property string|null $domain
 * @property string|null $path
 * @property array $params
 * @property string|null $anchor
 */
class Address
{
    /**
     * HTTP/HTTPS.
     * @var bool
     */
    protected bool $ssl = false;

    /**
     * Имя домена.
     * @var string
     */
    protected string $domain = '';

    /**
     * Относительный путь.
     * @var string
     */
    protected string $path = '';

    /**
     * GET-параметры.
     * @var array
     */
    protected array $params = [];

    /**
     * Якорь.
     * @var string
     */
    protected string $anchor = '';

    /**
     * @param string $url - адрес
     */
    public function __construct(string $url = '')
    {
        $parts = parse_url($url);

        if (isset($parts['scheme'])) {
            $this->ssl = $parts['scheme'] == 'https';
        }

        if (isset($parts['host'])) {
            $this->domain = $parts['host'];
        }

        if (isset($parts['path'])) {
            $this->path = $parts['path'];
        }

        if (isset($parts['query'])) {
            parse_str($parts['query'], $params);
            $this->params = $params;
        }

        if (isset($parts['fragment'])) {
            $this->anchor = $parts['fragment'];
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, mixed $value)
    {
        if ($name == 'params' && is_array($value)) {
            if (array_key_exists('#', $value)) {
                $this->anchor = empty($value['#']) ? '' : $value['#'];
                unset($value['#']);
            }

            $this->params = array_filter($value, function ($value) {
                return $value !== null;
            });
        } elseif ($name == 'ssl') {
            $this->ssl = (bool)$value;
        } elseif (property_exists($this, $name)) {
            $this->$name = (string)$value;
        }
    }

    /**
     * @param string $name
     * @return string|bool|null
     */
    public function __get(string $name)
    {
        return $this->$name;
    }

    /**
     * Сформировать адрес.
     * @param bool $params - включить параметры
     * @param bool $absolute - абсолютный адрес
     * @param bool $anchor - включить якорь
     * @return string
     */
    public function getUrl(bool $params = true, bool $absolute = false, bool $anchor = false): string
    {
        $url = '';

        if ($absolute && isset($this->domain)) {
            $url .= $this->ssl ? 'https://' : 'http://';
            $url .= $this->domain;
        }

        $url .= $this->path;

        if ($params && !empty($this->params)) {
            $url .= '?' . http_build_query($this->params, '', '&');
        }

        if ($anchor && !empty($this->anchor)) {
            $url .= '#' . $this->anchor;
        }

        return $url;
    }
}
