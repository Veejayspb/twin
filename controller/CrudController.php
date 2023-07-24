<?php

namespace twin\controller;

use twin\common\Exception;
use twin\helper\Pagination;
use twin\helper\Request;
use twin\helper\StringHelper;
use twin\helper\Url;
use twin\model\active\ActiveModel;
use twin\model\query\Query;
use twin\widget\PaginationWidget;

abstract class CrudController extends WebController
{
    const VIEW_PATH = '@twin/controller/view/crud';

    /**
     * Список.
     * @return string
     */
    public function index()
    {
        $modelName = $this->getModelName();
        $query = $modelName::find(); /* @var Query $query */
        $page = (int)Request::get(PaginationWidget::DEFAULT_PARAMETER, 1);;
        $pagination = new Pagination($query->count(), $page, 10);
        $models = $pagination->apply($query)->all();
        $this->getView()->path = static::VIEW_PATH;

        return $this->render('index', [
            'query' => $query,
            'models' => $models,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Просмотр.
     * @param int $id
     * @return string
     * @throws Exception
     */
    public function view($id)
    {
        $modelName = $this->getModelName();
        $model = $modelName::findByAttributes(['id' => $id])->one(); /* @var ActiveModel $model */

        if (!$model) {
            throw new Exception(404);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Добавление.
     * @return string
     */
    public function create()
    {
        $modelName = $this->getModelName();
        $modelNameShort = StringHelper::getClassName($modelName);
        $model = new $modelName; /* @var ActiveModel $model */
        $attributes = Request::post($modelNameShort, []);

        if ($attributes && $model->load($attributes)) {
            $this->redirect(Url::to('index'));
        }

        $form = $this->renderForm($model);
        $this->getView()->path = static::VIEW_PATH;

        return $this->render('create', [
            'form' => $form,
        ]);
    }

    /**
     * Изменение.
     * @param int $id
     * @return string
     * @throws Exception
     */
    public function update($id)
    {
        $modelName = $this->getModelName();
        $modelNameShort = StringHelper::getClassName($modelName);
        $model = $modelName::findByAttributes(['id' => $id])->one(); /* @var ActiveModel $model */
        $attributes = Request::post($modelNameShort, []);

        if (!$model) {
            throw new Exception(404);
        }

        if ($attributes && $model->load($attributes)) {
            $this->redirect(Url::to('index'));
        }

        $form = $this->renderForm($model);
        $this->getView()->path = static::VIEW_PATH;

        return $this->render('update', [
            'form' => $form,
        ]);
    }

    /**
     * Удаление.
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function delete($id)
    {
        $modelName = $this->getModelName();
        $model = $modelName::findByAttributes(['id' => $id])->one(); /* @var ActiveModel $model */

        if (!$model) {
            throw new Exception(404);
        }

        $model->delete();
        $this->redirect(Url::to('index'));
    }

    /**
     * Название модели.
     * @return string
     */
    abstract protected function getModelName(): string;

    /**
     * Рендер шаблона с формой.
     * @param ActiveModel $model
     * @return string
     */
    abstract protected function renderForm(ActiveModel $model): string;
}
