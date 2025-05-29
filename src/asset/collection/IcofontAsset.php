<?php

namespace twin\asset\collection;

use twin\asset\Asset;
use twin\helper\Html;

class IcofontAsset extends Asset
{
    /**
     * {@inheritdoc}
     */
    public array $css = [
        '{main}/icofont/icofont.min.css',
    ];

    /**
     * {@inheritdoc}
     */
    public array $publish = [
        'main' => '@twin/asset/collection/src',
    ];

    /**
     * Сгенерировать иконку.
     * @param string $name - название
     * @param int $size - размер от 1 до 5
     * @param array $htmlAttributes - HTML-атрибуты
     * @return string
     */
    public static function icon(string $name, int $size = 1, array $htmlAttributes = []): string
    {
        self::register();
        Html::addCssClass($htmlAttributes, "icofont-$name");
        Html::addCssClass($htmlAttributes, "icofont-{$size}x");
        return Html::tag('i', $htmlAttributes);
    }
}
