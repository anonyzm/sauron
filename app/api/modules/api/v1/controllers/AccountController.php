<?php

namespace api\modules\api\v1\controllers;

use api\modules\api\v1\controllers\base\BaseActiveController;
use api\modules\api\v1\exceptions\ApiException;
use api\modules\api\v1\models\AccountSearch;
use common\models\Account;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class AccountController extends BaseActiveController
{
    public $modelClass = Account::class;
    public $searchModelClass = AccountSearch::class;

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    public function actionCreate()
    {
        /* @var $model Account */
        $model = new $this->modelClass([
            'service_id' => Yii::$app->user->id,
        ]);

        $body = Yii::$app->request->bodyParams;
        $model->load($body, '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the account for unknown reason.');
        } else {
            \Yii::error($model->errors);
            throw new ApiException($model->errors, 'Could not create account');
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        /* @var $model Account */
        $model = $this->findModel($id);

        $body = Yii::$app->request->bodyParams;
        $model->load($body, '');

        if (!$model->save() && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the account for unknown reason.');
        }

        if ($model->hasErrors()) {
            throw new ApiException($model->errors, 'Could not update account');
        }

        return $model;
    }

    public function findModel($id)
    {
        /** @var Account $model */
        $model = parent::findModel($id);

        if($model->service_id !== Yii::$app->user->identity->id) {
            throw new ForbiddenHttpException('You don\'t have access to the requested Account');
        }

        return $model;
    }
}
