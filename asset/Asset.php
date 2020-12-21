<?php

namespace twin\asset;

use twin\common\Exception;
use twin\helper\File;
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
    public $css = [];

    /**
     * Адреса или пути до JS-файлов.
     * /script.js
     * https://domain.ru/script.js
     * {lib}/script.js
     * @var array
     */
    public $js = [];

    /**
     * Пути до директорий, которые необходимо опубликовать.
     * Ключ - название плейсхолдера.
     * Значение - алиас пути до исходной директории.
     * ['lib' => '@app/lib']
     * @var array
     * @see $css, $js
     */
    public $publish = [];

    /**
     * Названия классов asset, от которых зависит данный.
     * @var array
     */
    public $depends = [];

    /**
     * Компонент управляющий asset.
     * @var AssetManager $assetManager
     */
    private $assetManager;

    /**
     * Адреса опубликованных asset и их плейсхолдеры.
     * Ключ - название плейсхолдера ресурса.
     * Значение - адрес ресурса.
     * @var array
     */
    private $placeholders = [];

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
    public static function register(): self
    {
        return Twin::app()->asset->register(static::class);
    }

    /**
     * Заменить плейсхолдер и вернуть адрес конкретного файла в директории досупной из WEB.
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
     * @param string|int $key - ключ, указанный в массиве $this->css
     * @return Tag
     * @see $css
     */
    protected function prepareCss(Tag $tag, $key): Tag
    {
        return $tag;
    }

    /**
     * Подготовить JS-тег для вывода в шаблон.
     * @param Tag $tag - исходный тег SCRIPT
     * @param string|int $key - ключ, указанный в массиве $this->js
     * @return Tag
     * @see $js
     */
    protected function prepareJs(Tag $tag, $key): Tag
    {
        return $tag;
    }

    /**
     * Генерация CRC32-хэша для использования в кач-ве названия новой директории.
     * @param string $path - путь до директории
     * @return string|bool - FALSE в случае отсутствия директории
     * @todo: если изменились вложенные директории, то дата изменения род. директории не изменится
     */
    protected function hash(string $path)
    {
        if (!is_dir($path)) return false;
        $str = $path . filemtime($path);
        return sprintf('%x', crc32($str . Twin::VERSION));
    }

    /**
     * Заменить плейсхолдеры в исходном адресе.
     * @param string $address - исходный адрес
     * @return string
     */
    private function prepareAddress(string $address): string
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
    private function publish()
    {
        foreach ($this->publish as $name => $path) {
            $from = Twin::getAlias($path);
            $hash = $this->hash($from);
            if (!$hash) {
                throw new Exception(500, "Asset path not exists: $from");
            }
            $alias = $this->assetManager->publicationPath . DIRECTORY_SEPARATOR . $hash;
            $to = Twin::getAlias($alias);
            $placeholder = '{' . $name . '}';
            $this->placeholders[$placeholder] = $this->assetManager->webPath . '/' . $hash;
            if (!$this->assetManager->force && is_dir($to)) continue; // Если asset уже опубликован
            if (!File::copy($from, $to)) {
                throw new Exception(500, "Can't publish asset: $from");
            }
        }
    }
}
