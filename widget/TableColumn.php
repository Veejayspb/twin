<?php

namespace twin\widget;

use twin\common\SetPropertiesTrait;

class TableColumn
{
    use SetPropertiesTrait;

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
        $this->setProperties($properties);
    }
}
