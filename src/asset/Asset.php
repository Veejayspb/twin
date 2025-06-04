<?php

namespace twin\asset;

use twin\common\Exception;
use twin\helper\Alias;
use twin\helper\file\Dir;
use twin\helper\Tag;
use twin\Twin;

abstract class Asset
{
    /**
     * Адреса или пути до CSS-файлов.
     * /style.css
     * https://domain.ru/style.css
     * {lib}/style.css
     * @var array
     *
     */
    public array $css = [];

    /**
     * Адреса или пути до JS-файлов.
     * /script.js
     * https://domain.ru/script.js
     * {lib}/script.js
     * @var array
     */
    public array $js = [];

    /**
     * Пути до директорий, которые необходимо опубликовать.
     * Ключ - название плейсхолдера.
     * Значение - алиас пути до исходной директории.
     * ['lib' => '@self/lib']
     * @var array
     * @see $css, $js
     */
    public array $publish = [];

    /**
     * Названия классов asset, от которых зависит данный.
     * @var array
     */
    public array $depends = [];

    /**
     * Компонент управляющий asset.
     * @var AssetManager $assetManager
     */
    private AssetManager $assetManager;

    /**
     * Адреса опубликованных asset и их плейсхолдеры.
     * Ключ - название плейсхолдера ресурса.
     * Значение - адрес ресурса.
     * @var array
     */
    private array $placeholders = [];

    /**
     * @param AssetManager $assetManager - компонент управляющий asset
     */
    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
        $this->publish();
    }

    /**
     * Регистрация текущего asset.
     * @return static
     */
    public static function register(): static
    {
        return Twin::app()->asset->register(static::class);
    }

    /**
     * Заменить плейсхолдер и вернуть адрес конкретного файла в директории доступной из WEB.
     * @param string $address - адрес вида: {placeholder}/dir/img.jpg
     * @return string
     */
    public static function address(string $address): string
    {
        $asset = static::register();
        return $asset->prepareAddress($address);
    }

    /**
     * Вернуть массив тегов со стилями.
     * @return Tag[]
     */
    public function css(): array
    {
        $result = [];

        foreach ($this->css as $key => $address) {
            $tag = new Tag('link', [
                'href' => $this->prepareAddress($address),
                'rel' => 'stylesheet',
            ]);

            $result[] = $this->prepareCss($tag, $key);
        }

        return $result;
    }

    /**
     * Вернуть массив тегов со скриптами.
     * @return Tag[]
     */
    public function js(): array
    {
        $result = [];

        foreach ($this->js as $key => $address) {
            $tag = new Tag('script', [
                'src' => $this->prepareAddress($address),
            ]);

            $result[] = $this->prepareJs($tag, $key);
        }

        return $result;
    }

    /**
     * Подготовить CSS-тег для вывода в шаблон.
     * @param Tag $tag - исходный тег LINK
     * @param int|string $key - ключ, указанный в массиве $this->css
     * @return Tag
     * @see $css
     */
    protected function prepareCss(Tag $tag, int|string $key): Tag
    {
        return $tag;
    }

    /**
     * Подготовить JS-тег для вывода в шаблон.
     * @param Tag $tag - исходный тег SCRIPT
     * @param int|string $key - ключ, указанный в массиве $this->js
     * @return Tag
     * @see $js
     */
    protected function prepareJs(Tag $tag, int|string $key): Tag
    {
        return $tag;
    }

    /**
     * Генерация CRC32-хэша для использования в кач-ве названия новой директории.
     * @param string $path - путь до директории
     * @return string|null - NULL в случае отсутствия директории
     * @todo: если изменились вложенные директории, то дата изменения род. директории не изменится
     */
    protected function hash(string $path): ?string
    {
        if (!is_dir($path)) {
            return null;
        }

        $str = $path . filemtime($path);
        return sprintf('%x', crc32($str . Twin::VERSION));
    }

    /**
     * Заменить плейсхолдеры в исходном адресе.
     * @param string $address - исходный адрес
     * @return string
     */
    protected function prepareAddress(string $address): string
    {
        $keys = array_keys($this->placeholders);
        return str_replace($keys, $this->placeholders, $address);
    }

    /**
     * Публикация директорий с asset и сохранение плейсхолдеров.
     * @return void
     * @throws Exception
     * @see $placeholders
     */
    protected function publish(): void
    {
        foreach ($this->publish as $name => $path) {
            $from = Alias::get($path);
            $hash = $this->hash($from);

            if (!$hash) {
                throw new Exception(500, "Asset path not exists: $from");
            }

            $alias = $this->assetManager->publicationPath . DIRECTORY_SEPARATOR . $hash;
            $to = Alias::get($alias);
            $placeholder = '{' . $name . '}';
            $this->placeholders[$placeholder] = $this->assetManager->webPath . '/' . $hash;

            // Если asset уже опубликован
            if (!$this->assetManager->force && is_dir($to)) {
                continue;
            }

            $dir = new Dir($from);

            if (!is_dir($to) && !mkdir($to, 0775, true)) {
                throw new Exception(500, "Can't create dir to publish asset: $to");
            }

            if (!$dir->copyInner($to, $this->assetManager->force)) {
                throw new Exception(500, "Can't publish asset: $from");
            }
        }
    }
}
