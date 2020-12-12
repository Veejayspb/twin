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
    private $ssl = false;

    /**
     * Имя домена.
     * @var string|null
     */
    private $domain = '';

    /**
     * Относительный путь.
     * @var string|null
     */
    private $path = '';

    /**
     * GET-параметры.
     * @var array
     */
    private $params = [];

    /**
     * Якорь.
     * @var string|null
     */
    private $anchor = '';

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
    public function __set($name, $value)
    {
        if ($name == 'params' && is_array($value)) {
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
    public function __get($name)
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
            $url.= $this->ssl ? 'https://' : 'http://';
            $url.= $this->domain;
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

    /**
     * Инстанс строителя адреса.
     * @return AddressBuilder
     */
    public function build(): AddressBuilder
    {
        return new AddressBuilder($this);
    }
}

/**
 * Class AddressBuilder
 *
 * @method self scheme(bool $value) - указать наличие протокола
 * @method self domain(bool $value) - указать наличие домена
 * @method self path(bool $value) - указать наличие пути
 * @method self params(bool $value) - указать наличие параметров
 * @method self anchor(bool $value) - указать наличие якоря
 */
final class AddressBuilder
{
    /**
     * Включенные в адрес части.
     * @var array
     */
    private $parts = [
        'scheme' => false,
        'domain' => false,
        'path' => true,
        'params' => false,
        'anchor' => false,
    ];

    /**
     * Объект с адресом.
     * @var Address
     */
    private $address;

    /**
     * @param Address $address
     */
    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->parts) && !empty($arguments)) {
            $this->parts[$name] = (bool)$arguments[0];
        }
        return $this;
    }

    /**
     * Добавить протокол и домен.
     * @param bool $value
     * @return self
     */
    public function absolute(bool $value = true): self
    {
        return $this->scheme($value)->domain($value);
    }

    /**
     * Сгенерировать адрес.
     * @return string
     */
    public function get(): string
    {
        $this->fillParts();
        $address = $this->address;

        $url = '';
        if ($this->parts['scheme'] && $address->domain) {
            $url.= $address->ssl ? 'https://' : 'http://';
        }
        if ($this->parts['domain'] && $address->domain) {
            $url.= $address->domain;
        }
        if ($this->parts['path']) {
            $url.= $address->path;
        }
        if ($this->parts['params']) {
            $url.= '?' . http_build_query($address->params, '', '&');
        }
        if ($this->parts['anchor']) {
            $url.= '#' . $address->anchor;
        }
        return $url;
    }

    /**
     * Включить в адрес все параметры, находящиеся между первым и последним входящими в адрес параметрами.
     * @return void
     */
    private function fillParts()
    {
        // Определение первого и последнего входящих в адрес параметров.
        $from = $to = null;
        foreach ($this->parts as $name => $include) {
            if ($include) {
                $to = $name;
                if ($from === null) {
                    $from = $name;
                }
            }
        }
        // Включить параметры.
        $started = false;
        foreach ($this->parts as $name => $include) {
            if ($name == $from) $started = true;
            $this->parts[$name] = $started;
            if ($name == $to) break;
        }

    }
}
