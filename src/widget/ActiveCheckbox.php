<?php

namespace twin\widget;

use twin\asset\Asset;
use twin\asset\collection\JqueryAsset;
use twin\helper\Html;

class ActiveCheckbox extends ActiveWidget
{
    /**
     * CSS-класс.
     */
    const CSS_CLASS = 'twin-active-checkbox';

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        ActiveCheckboxAsset::register();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::checkbox(null, $this->getHtmlAttributes());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHtmlAttributes(): array
    {
        $htmlAttributes = parent::getHtmlAttributes();
        $htmlAttributes['checked'] = (bool)$this->value;
        return $htmlAttributes;
    }
}

class ActiveCheckboxAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        '{main}/ActiveCheckbox/script.js',
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
