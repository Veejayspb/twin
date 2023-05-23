<?php

namespace twin\view;

use twin\common\Component;
use twin\helper\Html;
use twin\Twin;

class View extends Component
{
    use RenderTrait;

    /**
     * Плейсхолдер выводится перед закрывающим тегом </HEAD>.
     */
    const HEAD = '<![CDATA[TWIN-HEAD]]>';

    /**
     * Плейсхолдер выводится перед закрывающим тегом </BODY>.
     */
    const BODY = '<![CDATA[TWIN-BODY]]>';

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
    public $path = '@self/view';

    /**
     * Расположение скриптов - в BODY.
     * Если FALSE, то скрипты будут размещены в HEAD.
     * @var bool
     */
    public $scriptBody = true;

    /**
     * Заголовок страницы.
     * @var string
     */
    public $title = 'Page title';

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
     */
    public function render(string $route, array $data = []): string
    {
        $alias = $this->path . DIRECTORY_SEPARATOR . $route . '.php';
        return $this->renderPath($alias, $data);
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
        $content = $this->render($layout, ['content' => $content]);

        return str_replace(
            [static::HEAD, static::BODY],
            [$this->head(), $this->body()],
            $content
        );
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

    /**
     * Вернуть контент для вывода в HEAD.
     * @return string
     */
    protected function head(): string
    {
        $items = [];

        // Asset CSS.
        $items = array_merge($items, Twin::app()->asset->getCss());

        // Asset JS.
        if (!$this->scriptBody) {
            $items = array_merge($items, Twin::app()->asset->getJs());
        }

        // Дополнительный контент.
        $items = array_merge($items, $this->head);

        return implode(PHP_EOL . Html::TAB, $items) . PHP_EOL;
    }

    /**
     * Вернуть контент для вывода в BODY.
     * @return string
     */
    protected function body(): string
    {
        $items = [];

        // Asset JS.
        if ($this->scriptBody) {
            $items = array_merge($items, Twin::app()->asset->getJs());
        }

        // Дополнительный контент.
        $items = array_merge($items, $this->body);

        return implode(PHP_EOL . Html::TAB, $items) . PHP_EOL;
    }
}
