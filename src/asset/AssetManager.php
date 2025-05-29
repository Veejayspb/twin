<?php

namespace twin\asset;

use twin\common\Component;
use twin\common\Exception;
use twin\helper\Tag;

class AssetManager extends Component
{
    /**
     * Путь до директории с asset доступной из WEB.
     * @var string
     */
    public string $publicationPath = '@public/asset';

    /**
     * Адрес директории с asset доступной из WEB.
     * @var string
     */
    public string $webPath = '/asset';

    /**
     * Принудительная публикация asset.
     * @var bool
     */
    public bool $force = false;

    /**
     * Добавленные asset.
     * @var Asset[]
     */
    protected array $assets = [];

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['publicationPath', 'webPath'];

    /**
     * Зарегистрировать asset.
     * @param string $name - название подключаемого класса asset
     * @return Asset
     * @throws Exception
     */
    public function register(string $name): Asset
    {
        if ($this->has($name)) {
            return $this->assets[$name];
        }

        if (!class_exists($name)) {
            throw new Exception(500, "Can't found asset class: $name");
        }

        if (!is_subclass_of($name, Asset::class)) {
            throw new Exception(500, "$name must extends " . Asset::class);
        }

        $asset = new $name($this); /* @var Asset $asset */

        // Регистрация asset, от которых зависит текущий
        foreach ($asset->depends as $class) {
            $this->register($class);
        }

        return $this->assets[$name] = $asset;
    }

    /**
     * Зарегистрирован ли asset.
     * @param string $name - название класса asset
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->assets);
    }

    /**
     * Очистить список asset.
     * @return void
     */
    public function clear(): void
    {
        $this->assets = [];
    }

    /**
     * Массив asset в иерархическом порядке.
     * @return Asset[]
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Вернуть все подключенные CSS в виде HTML-тегов в иерархическом порядке.
     * @return Tag[]
     */
    public function getCss(): array
    {
        $result = [];
        $assets = $this->getAssets();

        foreach ($assets as $asset) {
            $result = array_merge($result, $asset->css());
        }

        return $result;
    }

    /**
     * Вернуть все подключенные JS в виде HTML-тегов в иерархическом порядке.
     * @return Tag[]
     */
    public function getJs(): array
    {
        $result = [];
        $assets = $this->getAssets();

        foreach ($assets as $asset) {
            $result = array_merge($result, $asset->js());
        }

        return $result;
    }
}
