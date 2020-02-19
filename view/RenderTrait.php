<?php

namespace twin\view;

use twin\Twin;

trait RenderTrait
{
    /**
     * Рендер вида по абсолютному пути.
     * @param string $path - путь/до/файла или алиас
     * @param array $data - данные
     * @return string
     */
    public function renderPath(string $path, array $data = []): string
    {
        $path = Twin::getAlias($path);
        if (file_exists($path)) {
            extract($data);
            ob_start();
            include $path;
            $content = ob_get_clean();
            return $content;
        }
        return '';
    }
}
