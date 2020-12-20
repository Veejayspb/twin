<?php

namespace twin\helper\file;

use finfo;
use twin\Twin;

class File
{
    const JPG = 'jpg';
    const PNG = 'png';
    const GIF = 'gif';
    const SVG = 'svg';
    const TIFF = 'tiff';
    const ICO = 'ico';
    const BMP = 'bmp';
    const WBMP = 'wbmp';
    const WEBP = 'webp';

    /**
     * Путь до файла.
     * @var string
     */
    public $path;

    /**
     * Расширения и MIME.
     * @var array
     */
    private static $extensions = [
        'image/jpeg' => self::JPG,
        'image/pjpeg' => self::JPG,
        'image/png' => self::PNG,
        'image/gif' => self::GIF,
        'image/svg+xml' => self::SVG,
        'image/tiff' => self::TIFF,
        'image/vnd.microsoft.icon' => self::ICO,
        'image/bmp' => self::BMP,
        'image/vnd.wap.wbmp' => self::WBMP,
        'image/webp' => self::WEBP,
    ];

    /**
     * @param string $path - путь до файла
     */
    public function __construct(string $path)
    {
        $this->path = Twin::getAlias($path);
    }

    /**
     * Скопировать файл.
     * @param string $path - путь для копирования: path/to/file.txt
     * @return static|bool - FALSE в случае ошибки
     */
    public function copy(string $path)
    {
        if (!$this->exists()) return false;
        if (@copy($this->path, $path)) {
            return new static($path);
        }
        return false;
    }

    /**
     * Переместить файл.
     * @param string $path - путь для перемещения: path/to/file.txt
     * @return bool
     */
    public function move(string $path): bool
    {
        if ($path == $this->path) return true;
        if (@copy($this->path, $path)) {
            @unlink($this->path);
            $this->path = $path;
            return true;
        }
        return false;
    }

    /**
     * Переименовать файл.
     * @param string $name - новое имя файла
     * @return bool
     */
    public function rename(string $name): bool
    {
        if (!$this->exists()) return false;
        $newPath = dirname($this->path) . DIRECTORY_SEPARATOR . basename($name);
        $result = @rename($this->path, $name);
        $this->path = $newPath;
        return $result;
    }

    /**
     * Удалить файл.
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists()) return true;
        return @unlink($this->path);
    }

    /**
     * Вернуть содержимое файла.
     * @return string|bool - FALSE в случае ошибки
     */
    public function getContent()
    {
        return @file_get_contents($this->path);
    }

    /**
     * Вернуть MIME-тип файла.
     * @return string|bool - FALSE в случае ошибки
     */
    public function getMimeType()
    {
        $content = $this->getContent();
        if ($content === false) return false;
        $info = new finfo(FILEINFO_MIME_TYPE);
        return $info->buffer($content);
    }

    /**
     * Расширение файла.
     * @return string|bool - FALSE в случае ошибки
     */
    public function getExt()
    {
        $mime = $this->getMimeType();
        if ($mime === false) return false;
        return array_key_exists($mime, static::$extensions) ? static::$extensions[$mime] : false;
    }

    /**
     * Удалить расширенную информацию о файле.
     * Работает с jpg, png, gif.
     * @return bool
     */
    public function removeExif(): bool
    {
        $ext = $this->getExt();
        switch ($ext) {
            case self::JPG:
                $img = imagecreatefromjpeg($this->path);
                return imagejpeg($img, $this->path, 100);
            case self::PNG:
                $img = imagecreatefrompng($this->path);
                return imagepng($img, $this->path, 0);
            case self::GIF:
                $img = imagecreatefromgif($this->path);
                return imagegif($img, $this->path);
        }
        return false;
    }

    /**
     * Существует ли файл.
     * @return bool
     */
    protected function exists(): bool
    {
        return is_file($this->path);
    }
}
