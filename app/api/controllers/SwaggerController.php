<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SwaggerController extends Controller
{
    public function init() {
        // закрываем этот эндпоинт для продакшена
        if(!YII_ENV_DEV) {
            throw new NotFoundHttpException();
        }
        parent::init();
    }

    /**
     * Это нужно для того чтобы корректно работал CORS, запрос на OPTIONS должен идти без авторизации
     * @param \yii\base\Action $action
     * @return bool|void
     */
    public function beforeAction($action)
    {
        $response = Yii::$app->response;
        $response->headers->add('Access-Control-Allow-Origin', '*');
        $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, DELETE, PUT, PATCH, OPTIONS');
        $response->headers->add('Access-Control-Allow-Headers', 'Content-Type, api_key, Authorization');
        if(Yii::$app->request->isOptions) {
            return;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex() {
        return $this->renderFile(realpath(__DIR__) . '/../docs/sauron/openapi.yaml');
    }
}