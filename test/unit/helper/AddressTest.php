<?php

namespace test\unit\helper;

use test\helper\BaseTestCase;
use twin\helper\Address;

final class AddressTest extends BaseTestCase
{
    const URL = 'https://sub.domain.ru/index.php?key=value#anchor';
    const SSL = true;
    const DOMAIN = 'sub.domain.ru';
    const PATH = '/index.php';
    const PARAMS = ['key' => 'value'];
    const ANCHOR = 'anchor';

    public function testConstruct()
    {
        $address = $this->getAddress(self::URL);

        $this->assertSame(self::SSL, $address->getProperty('ssl'));
        $this->assertSame(self::DOMAIN, $address->getProperty('domain'));
        $this->assertSame(self::PATH, $address->getProperty('path'));
        $this->assertSame(self::PARAMS, $address->getProperty('params'));
        $this->assertSame(self::ANCHOR, $address->getProperty('anchor'));
    }

    public function testSet()
    {
        $address = $this->getAddress();

        $address->ssl = self::SSL;
        $this->assertSame(self::SSL, $address->getProperty('ssl'));

        $address->domain = self::DOMAIN;
        $this->assertSame(self::DOMAIN, $address->getProperty('domain'));

        $address->path = self::PATH;
        $this->assertSame(self::PATH, $address->getProperty('path'));

        $address->params = self::PARAMS;
        $this->assertSame(self::PARAMS, $address->getProperty('params'));

        $address->anchor = self::ANCHOR;
        $this->assertSame(self::ANCHOR, $address->getProperty('anchor'));
    }

    public function testGet()
    {
        $address = $this->getAddress();

        $address->setProperty('ssl', self::SSL);
        $this->assertSame(self::SSL, $address->ssl);

        $address->setProperty('domain', self::DOMAIN);
        $this->assertSame(self::DOMAIN, $address->domain);

        $address->setProperty('path', self::PATH);
        $this->assertSame(self::PATH, $address->path);

        $address->setProperty('params', self::PARAMS);
        $this->assertSame(self::PARAMS, $address->params);

        $address->setProperty('anchor', self::ANCHOR);
        $this->assertSame(self::ANCHOR, $address->anchor);
    }

    public function testGetUrl()
    {
        $address = $this->getAddress(self::URL);

        $url = $address->getUrl(true, false, false);
        $this->assertSame('/index.php?key=value', $url);

        $url = $address->getUrl(false, true, true);
        $this->assertSame('https://sub.domain.ru/index.php#anchor', $url);
    }

    /**
     * @param string $url
     * @return Address
     */
    private function getAddress(string $url = '')
    {
        return new class ($url) extends Address
        {
            public function getProperty(string $name)
            {
                return $this->$name;
            }

            public function setProperty(string $name, $value)
            {
                $this->$name = $value;
            }
        };
    }
}
