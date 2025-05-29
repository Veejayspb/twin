<?php

namespace twin\view;

use twin\common\Component;
use twin\helper\Html;
use twin\helper\Tag;
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
     * Алиас пути до шаблона.
     * @var string
     */
    public string $layoutPath = '@self/view/layout/main.php';

    /**
     * Алиас пути до директории с видами.
     * @var string
     */
    public string $alias = '@self/view';

    /**
     * Расположение скриптов - в BODY.
     * Если FALSE, то скрипты будут размещены в HEAD.
     * @var bool
     */
    public bool $scriptBody = true;

    /**
     * Заголовок страницы.
     * @var string
     */
    public string $title = 'Page title';

    /**
     * Хлебные крошки.
     * @var array
     */
    public array $breadcrumbs = ['Главная' => '/'];

    /**
     * Дополнительный контент для вывода в HEAD.
     * @var array
     */
    protected array $head = [];

    /**
     * Дополнительный контент для вывода в BODY.
     * @var array
     */
    protected array $body = [];

    /**
     * {@inheritdoc}
     */
    protected array $_requiredProperties = ['layoutPath', 'alias'];

    /**
     * Рендер вида без шаблона.
     * @param string $route - текстовый роут
     * @param array $data - данные
     * @return string
     */
    public function render(string $route, array $data = []): string
    {
        $alias = $this->alias . '/' . $route . '.php';
        return $this->renderPath($alias, $data);
    }

    /**
     * Рендер вида с шаблоном.
     * @param string $route - текстовый роут
     * @param array $data - данные
     * @return string
     */
    public function renderLayout(string $route, array $data = []): string
    {
        $content = $this->render($route, $data);

        $content = $this->renderPath($this->layoutPath, [
            'content' => $content,
        ]);

        return str_replace(
            [static::HEAD, static::BODY],
            [$this->head(), $this->body()],
            $content
        );
    }

    /**
     * Начало родительского шаблона.
     * @return bool
     */
    public function begin(): bool
    {
        return ob_start();
    }

    /**
     * Конец родительского шаблона.
     * @param string $alias - алиас пути до родительского шаблона
     * @return string
     */
    public function end(string $alias): string
    {
        $content = ob_get_clean();

        return $this->renderPath($alias, [
            'content' => $content,
        ]);
    }

    /**
     * Добавить контент для вывода в HEAD.
     * @param string $str
     * @return void
     */
    public function addHead(string $str): void
    {
        $this->head[] = $str;
    }

    /**
     * Добавить контент для вывода в BODY.
     * @param string $str
     * @return void
     */
    public function addBody(string $str): void
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
        $items = array_merge($items, $this->getCss());

        // Asset JS.
        if (!$this->scriptBody) {
            $items = array_merge($items, $this->getJs());
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
            $items = array_merge($items, $this->getJs());
        }

        // Дополнительный контент.
        $items = array_merge($items, $this->body);

        return implode(PHP_EOL . Html::TAB, $items) . PHP_EOL;
    }

    /**
     * Массив CSS-тегов, закрепленных через assets.
     * @return Tag[]
     */
    protected function getCss(): array
    {
        return Twin::app()->asset->getCss();
    }

    /**
     * Массив JS-тегов, закрепленных через assets.
     * @return Tag[]
     */
    protected function getJs(): array
    {
        return Twin::app()->asset->getJs();
    }
}
