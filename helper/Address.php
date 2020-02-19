<?php

namespace core\helper;

/**
 * Class Address
 * @package core\helper
 *
 * @property bool $ssl
 * @property string|null $host
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
    private $ssl = false;

    /**
     * Имя хоста.
     * @var string|null
     */
    private $host;

    /**
     * Относительный путь.
     * @var string|null
     */
    private $path;

    /**
     * GET-параметры.
     * @var array
     */
    private $params = [];

    /**
     * Якорь.
     * @var string|null
     */
    private $anchor;

    /**
     * @param string|null $url - адрес
     */
    public function __construct(string $url = null)
    {
        if ($url !== null) {
            $parts = parse_url($url);
            if (isset($parts['scheme'])) {
                $this->ssl = $parts['scheme'] == 'https';
            }
            if (isset($parts['host'])) {
                $this->host = $parts['host'];
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
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (!property_exists($this, $name)) return;

        if ($name == 'params' && is_array($value)) {
            $this->params = array_filter($value, function ($value) {
                return $value !== null;
            });
        } else {
            $this->$name = $value;
        }
    }

    /**
     * @param string $name
     * @return string|bool|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }

    /**
     * Сформировать адрес.
     * @param bool $params - включить параметры
     * @param bool $absolute - абсолютный адрес
     * @param bool $anchor - включить якорь
     * @return string
     */
    public function getUrl(bool $params = true, bool $absolute = false, bool $anchor = false)
    {
        $url = '';
        if ($absolute && isset($this->host)) {
            $url.= $this->ssl ? 'https://' : 'http://';
            $url.= $this->host;
        }
        $url.= $this->path;
        if ($params && !empty($this->params)) {
            $url.= '?' . http_build_query($this->params, '', '&');
        }
        if ($anchor && isset($this->anchor)) {
            $url.= '#' . $this->anchor;
        }
        return $url;
    }
}
