<?php

namespace twin\view;

use twin\Twin;

trait RenderTrait
{
    /**
     * Рендер вида по абсолютному пути.
     * @param string $alias - алиас
     * @param array $data - данные
     * @return string
     */
    public function renderPath(string $alias, array $data = []): string
    {
        $path = Twin::getAlias($alias);

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
