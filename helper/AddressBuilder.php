<?php

namespace twin\helper;

/**
 * Class AddressBuilder
 *
 * @method self scheme(bool $value = true) - указать наличие протокола
 * @method self domain(bool $value = true) - указать наличие домена
 * @method self path(bool $value = true) - указать наличие пути
 * @method self params(bool $value = true) - указать наличие параметров
 * @method self anchor(bool $value = true) - указать наличие якоря
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
        'path' => false,
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
        if (array_key_exists($name, $this->parts)) {
            $value = array_key_exists(0, $arguments) ? (bool)$arguments[0] : true;
            $this->parts[$name] = $value;
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
