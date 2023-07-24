<?php
/* @var View $this */
/* @var Query $query */
/* @var ActiveModel[] $models */
/* @var Pagination $pagination */

use twin\helper\Pagination;
use twin\model\active\ActiveModel;
use twin\model\query\Query;
use twin\view\View;
use twin\widget\TableColumn;
use twin\widget\TableModel;
?>

<?= new TableModel([
    'query' => $query,
    'htmlAttributes' => [
        'class' => 'table table-bordered',
    ],
    'columns' => [
        new TableColumn([
            'name' => 'id',
            'label' => 'ID',
            'value' => function (ActiveModel $item) {
                return $item->id;
            },
            'sort' => false,
        ]),
    ],
]) ?>
