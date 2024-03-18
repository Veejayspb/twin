<?php

use twin\helper\ConfigConstructor;
use twin\route\RouteManager;
use test\helper\BaseTestCase;
use test\helper\ObjectProxy;
use twin\Twin;
use twin\view\View;

final class ConfigConstructorTest extends BaseTestCase
{
    public function testConstruct()
    {
        $data = ['test'];
        $config = new ConfigConstructor($data);
        $proxy = new ObjectProxy($config);

        $this->assertSame($data, $proxy->data);
    }

    public function testGetData()
    {
        $config = new ConfigConstructor($this->getConfigData('custom'));
        $data = [
            'name' => 'custom',
            'params' => [
                'key' => 'value',
            ],
            'components' => [
                'route' => [
                    'namespaces' => [
                        '' => 'app\\controller',
                    ],
                    'class' => RouteManager::class,
                ],
                'view' => [
                    'class' => View::class,
                ],
            ],
            'language' => 'ru',
        ];

        $this->assertSame($this->getConfigData('custom', true), $config->getData());
        $this->assertSame($data, $config->getData(true));

        $config = new ConfigConstructor($this->getConfigData('web'));
        $data = [
            'name' => 'web',
            'language' => 'ru',
            'params' => [
                'key' => 'value',
            ],
            'components' => [
                'route' => [
                    'class' => RouteManager::class,
                ],
            ],
        ];

        $this->assertSame($this->getConfigData('web', true), $config->getData());
        $this->assertSame($data, $config->getData(true));

        $config = new ConfigConstructor($this->getConfigData('common'));
        $data = [
            'name' => 'common',
            'components' => [],
        ];

        $this->assertSame($this->getConfigData('common', true), $config->getData());
        $this->assertSame($data, $config->getData(true));
    }

    public function testGetParent()
    {
        $dataCommon = $this->getConfigData('common', true);
        $dataWeb = $this->getConfigData('web', true);
        $dataCustom = $this->getConfigData('custom');

        $config = new ConfigConstructor($dataCustom);
        $parent = $config->getParent();

        $this->assertSame(ConfigConstructor::class, get_class($parent));
        $this->assertSame($dataWeb, $parent->getData());

        $parent = $parent->getParent();

        $this->assertSame(ConfigConstructor::class, get_class($parent));
        $this->assertSame($dataCommon, $parent->getData());

        $parent = $parent->getParent();

        $this->assertNull($parent);
    }

    /**
     * @param string $name
     * @param bool $withoutParent
     * @return array|bool
     */
    protected function getConfigData(string $name, bool $withoutParent = false)
    {
        $data = Twin::import("@test/helper/config/{$name}.php");

        if ($withoutParent && is_array($data) && array_key_exists('parent', $data)) {
            unset($data['parent']);
        }

        return $data;
    }
}
