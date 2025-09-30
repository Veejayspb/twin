<?php

namespace twin\asset;

use twin\common\Exception;
use twin\helper\Tag;

class AssetManager
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
     * Зарегистрировать asset.
     * @param string $class - название подключаемого класса asset
     * @return Asset
     * @throws Exception
     */
    public function register(string $class): Asset
    {
        if ($this->has($class)) {
            return $this->assets[$class];
        }

        if (!class_exists($class)) {
            throw new Exception(500, "Can't found asset class: $class");
        }

        if (!is_subclass_of($class, Asset::class)) {
            throw new Exception(500, "$class must extends " . Asset::class);
        }

        $asset = new $class($this); /* @var Asset $asset */

        // Регистрация asset, от которых зависит текущий
        foreach ($asset->depends as $depends) {
            $this->register($depends);
        }

        return $this->assets[$class] = $asset;
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
