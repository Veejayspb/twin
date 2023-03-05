<?php

namespace twin\view;

use twin\helper\Alias;

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
        $path = Alias::get($alias);

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
