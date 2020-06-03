<?php

namespace twin\view;

use twin\common\Component;
use twin\common\Exception;
use twin\helper\Html;
use twin\Twin;

class View extends Component
{
    use RenderTrait;

    const POS_HEAD = 'head';
    const POS_BODY = 'body';

    /**
     * Название шаблона.
     * @var string
     */
    public $layout = 'main';

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
     * Расположение скриптов.
     * @var string - head|body
     */
    public $scriptPosition = self::POS_BODY;

    /**
     * Заголовок страницы.
     * @var string
     */
    public $title = 'Page title';

    /**
     * Заголовок H1.
     * @var string
     */
    public $h1 = 'Header';

    /**
     * Хлебные крошки.
     * @var array
     */
    public $breadcrumbs = ['Главная' => '/'];

    /**
     * Дополнительный контент для вывода в HEAD.
     * @var array
     */
    protected $head = [];

    /**
     * Дополнительный контент для вывода в BODY.
     * @var array
     */
    protected $body = [];

    /**
     * Рендер вида без шаблона.
     * @param string $route - роут
     * @param array $data - данные
     * @return string
     * @throws Exception
     */
    public function render(string $route, array $data = []): string
    {
        $alias = $this->path . DIRECTORY_SEPARATOR . $route . '.php';
        $path = Twin::getAlias($alias);
        if (!is_file($path)) {
            throw new Exception(500, "View file not found: $path");
        }
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

    /**
     * Вернуть контент для вывода в HEAD.
     * Данный метод необходимо вставить в шаблон перед закрывающим тегом </head>.
     * @return string
     */
    public function head(): string
    {
        $items = [];

        // Asset CSS.
        $items = array_merge($items, Twin::app()->asset->getCss());

        // Asset JS.
        if ($this->scriptPosition == static::POS_HEAD) {
            $items = array_merge($items, Twin::app()->asset->getJs());
        }

        // Дополнительный контент.
        $items = array_merge($items, $this->head);

        return implode(PHP_EOL . Html::TAB, $items);
    }

    /**
     * Вернуть контент для вывода в BODY.
     * Данный метод необходимо вставить в шаблон перед закрывающим тегом </body>.
     * @return string
     */
    public function body(): string
    {
        $items = [];

        // Asset JS.
        if ($this->scriptPosition == static::POS_BODY) {
            $items = array_merge($items, Twin::app()->asset->getJs());
        }

        // Дополнительный контент.
        $items = array_merge($items, $this->body);

        return implode(PHP_EOL . Html::TAB, $items);
    }

    /**
     * Добавить контент для вывода в HEAD.
     * @param string $str
     * @return void
     */
    public function addHead(string $str)
    {
        $this->head[] = $str;
    }

    /**
     * Добавить контент для вывода в BODY.
     * @param string $str
     * @return void
     */
    public function addBody(string $str)
    {
        $this->body[] = $str;
    }
}
