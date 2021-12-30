<?php

namespace twin\widget;

use twin\common\Component;

class TableColumn extends Component
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
}
