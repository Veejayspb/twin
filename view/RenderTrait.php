<?php

namespace twin\view;

use twin\common\Exception;
use twin\helper\Alias;

trait RenderTrait
{
    /**
     * Рендер вида по абсолютному пути.
     * @param string $alias - алиас
     * @param array $data - данные
     * @return string
     * @throws Exception
     */
    public function renderPath(string $alias, array $data = []): string
    {
        $path = Alias::get($alias);

        if (!is_file($path)) {
            throw new Exception(500, "View file not found: $path");
        }

        extract($data);
        ob_start();
        include $path;
        $content = ob_get_clean();

        return $content;
    }
}
