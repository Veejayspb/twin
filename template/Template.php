<?php

namespace twin\template;

class Template
{
    /**
     * Путь до шаблона.
     * @var string
     */
    protected $path;

    /**
     * @param string $path - путь до шаблона
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Сохранение шаблона.
     * @param string $path - путь для сохранения шаблона
     * @param array $params - параметры для замены
     * @return bool
     */
    public function save(string $path, array $params = []): bool
    {
        if (!is_file($this->path)) return false;
        $content = file_get_contents($this->path);
        if ($content === false) return false;
        $content = $this->replacePlaceholders($content, $params);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }
        return (bool)file_put_contents($path, $content);
    }

    /**
     * Заменить плейсхолдеры в тексте.
     * @param string $content - текст
     * @param array $params - параметры
     * @return string
     */
    private function replacePlaceholders(string $content, array $params = []): string
    {
        foreach ($params as $key => $value) {
            $placeholder = $this->createPlaceholder($key);
            $params[$placeholder] = $value;
            unset($params[$key]);
        }
        return str_replace(
            array_keys($params),
            $params,
            $content
        );
    }

    /**
     * Сгенерировать плейсхолдер по его названию.
     * @param string $name - название
     * @return string
     */
    private function createPlaceholder(string $name): string
    {
        return '{{' . $name . '}}';
    }
}
