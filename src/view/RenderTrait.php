<?php

namespace twin\view;

use twin\common\Exception;
use twin\helper\Alias;

trait RenderTrait
{
    /**
     * Рендер вида по алиасу пути до файла.
     * @param string $alias - алиас пути
     * @param array $data - данные, передаваемые в файл
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
        return ob_get_clean();
    }
}
