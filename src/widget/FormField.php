<?php

namespace twin\widget;

use twin\model\Model;

abstract class FormField extends Widget
{
    /**
     * Модель.
     * @var Model
     */
    public Model $model;

    /**
     * Название атрибута.
     * @var string
     */
    public string $attribute;

    /**
     * HTML-атрибуты.
     * @var array
     */
    public array $htmlAttributes = [];

    /**
     * Родительский виджет с формой.
     * @var FormWidget
     */
    public FormWidget $parent;
}
