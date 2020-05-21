<?php

namespace twin\view;

use twin\common\Component;
use twin\Twin;

class View extends Component
{
    use RenderTrait;

    /**
     * Название шаблона.
     * @var string
     */
    public $layout = 'main';

    /**
     * Заголовок страницы.
     * @var string
     */
    public $title = 'Page title';

    /**
     * Название директории с шаблонами.
     * @var string
     */
    public $layoutDir = 'layout';

    /**
     * Путь/алиас к директории с видами.
     * @var string
     */
    public $path = '@app/view';

    /**
     * Рендер вида без шаблона.
     * @param string $route - роут
     * @param array $data - данные
     * @return string
     */
    public function render(string $route, array $data = []): string
    {
        $alias = $this->path . DIRECTORY_SEPARATOR . $route . '.php';
        $path = Twin::getAlias($alias);
        return $this->renderPath($path, $data);
    }

    /**
     * Рендер вида с шаблоном.
     * @param string $route - роут
     * @param array $data - данные
     * @return string
     */
    public function renderLayout(string $route, array $data = []): string
    {
        $content = $this->render($route, $data);
        $layout = $this->layoutDir . '/' . $this->layout;
        return $this->render($layout, ['content' => $content]);
    }

    /**
     * Начало родительского шаблона.
     * @return void
     */
    public function start()
    {
        ob_start();
    }

    /**
     * Конец родительского шаблона.
     * @param string $layout - название родительского шаблона
     * @return void
     */
    public function end(string $layout)
    {
        $content = ob_get_clean();
        $route = $this->layoutDir . '/' . $layout;
        echo $this->render($route, ['content' => $content]);
    }
}
