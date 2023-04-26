<?php

namespace twin\widget;

use twin\helper\ObjectHelper;

class TableColumn
{
    /**
     * Название столбца.
     * @var string
     */
    public $name;

    /**
     * Заголовок столбца.
     * @var string
     */
    public $label;

    /**
     * Содержимое ячейки.
     * @var callable
     */
    public $value;

    /**
     * Возможность сортировки.
     * @var bool
     */
    public $sort = true;

    /**
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        ObjectHelper::setProperties($this, $properties);
    }
}
