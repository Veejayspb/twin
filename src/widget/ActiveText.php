<?php

namespace twin\widget;

use twin\asset\Asset;
use twin\asset\collection\JqueryAsset;
use twin\helper\Html;

class ActiveText extends ActiveWidget
{
    /**
     * CSS-класс.
     */
    const CSS_CLASS = 'twin-active-text';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        ActiveTextAsset::register();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::tag('span', $this->getHtmlAttributes(), $this->value);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHtmlAttributes(): array
    {
        $htmlAttributes = parent::getHtmlAttributes();
        $htmlAttributes['contenteditable'] = true;
        return $htmlAttributes;
    }
}

class ActiveTextAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        '{main}/ActiveText/script.js',
    ];

    /**
     * {@inheritdoc}
     */
    public $publish = [
        'main' => '@twin/widget/src',
    ];

    /**
     * {@inheritdoc}
     */
    public $depends = [
        JqueryAsset::class,
    ];
}
