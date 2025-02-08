<?php

namespace twin\widget;

use twin\model\Model;

abstract class FormField extends Widget
{
    /**
     * Модель.
     * @var Model
     */
    public $model;

    /**
     * Название атрибута.
     * @var string
     */
    public $attribute;

    /**
     * HTML-атрибуты.
     * @var array
     */
    public $htmlAttributes = [];

    /**
     * Родительский виджет с формой.
     * @var FormWidget
     */
    public $parent;
}
