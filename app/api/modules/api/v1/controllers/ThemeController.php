<?php

namespace api\modules\api\v1\controllers;

use api\modules\api\v1\controllers\base\BaseActiveController;
use api\modules\api\v1\exceptions\ApiException;
use api\modules\api\v1\models\ThemeSearch;
use common\models\Theme;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class ThemeController extends BaseActiveController
{
    public $modelClass = Theme::class;
    public $searchModelClass = ThemeSearch::class;

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    public function actionCreate()
    {
        /* @var $model Theme */
        $model = new $this->modelClass();

        $body = Yii::$app->request->bodyParams;
        $model->load($body, '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the theme for unknown reason.');
        } else {
            throw new ApiException($model->errors, 'Could not create theme');
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        /* @var $model Theme */
        $model = $this->findModel($id);

        $body = Yii::$app->request->bodyParams;
        // TODO: костыль, т.к. через http не передается пустой массив
        if(empty($body['minusWords'])) {
            $body['minusWords'] = [];
        }
        $model->load($body, '');

        if (!$model->save() && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the theme for unknown reason.');
        }

        if ($model->hasErrors()) {
            throw new ApiException($model->errors, 'Could not update theme');
        }

        return $model;
    }

    public function findModel($id)
    {
        /** @var Theme $model */
        $model = parent::findModel($id);

        if($model->account->service_id !== Yii::$app->user->identity->id) {
            throw new ForbiddenHttpException('You don\'t have access to the requested Theme');
        }

        return $model;
    }
}
