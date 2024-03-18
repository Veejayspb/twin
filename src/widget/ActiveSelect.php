<?php

namespace twin\widget;

use twin\asset\Asset;
use twin\asset\collection\JqueryAsset;
use twin\helper\Html;

class ActiveSelect extends ActiveWidget
{
    /**
     * CSS-класс.
     */
    const CSS_CLASS = 'twin-active-select';

    /**
     * Список опций.
     * @var array
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        ActiveSelectAsset::register();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): string
    {
        return Html::select($this->value, $this->options, $this->getHtmlAttributes());
    }
}

class ActiveSelectAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public $js = [
        '{main}/ActiveSelect/script.js',
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
