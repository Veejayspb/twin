<?php

namespace core\view;

use core\common\Component;
use core\common\Exception;
use core\Twin;

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
    protected $viewPath = '@app/view';

    /**
     * Установить путь/алиас до директории с видами.
     * @param string $path - путь до директории с видами
     * @return void
     */
    public function setViewPath(string $path)
    {
        $this->viewPath = $path;
    }

    /**
     * Рендер вида без шаблона.
     * @param string $route - роут
     * @param array $data - данные
     * @return string
     */
    public function render(string $route, array $data = []): string
    {
        $alias = $this->viewPath . DIRECTORY_SEPARATOR . "$route.php";
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
        $layout = "$this->layoutDir/$this->layout";
        return $this->render($layout, ['content' => $content]);
    }

    /**
     * Стили и скрипты в HEAD.
     * @return string
     * @throws Exception
     */
    public function head()
    {
        // TODO...
    }

    /**
     * Скрипты в BODY.
     * @return string
     * @throws Exception
     */
    public function body()
    {
        // TODO...
    }
}
