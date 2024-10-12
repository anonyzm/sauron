<?php
namespace api\controllers;

use common\models\Account;
use common\models\Theme;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class ZabbixController extends Controller
{
    public function actionQueueMessages($vhost = '%2F') {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $queues = [
            'operator' => 0,
            'persister' => 0,
            'presentation' => 0,
        ];

        foreach ($queues as $key=>$queue) {
            $response = Yii::$app->rabbit->api->queue($key.'-exchange.queue', $vhost);
            $queues[$key] = ArrayHelper::getValue($response, 'messages');
        }

        return $queues;
    }

    public function actionInfo() {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'accounts' => Account::find()->count(),
            'themesTotal' => Theme::find()->count(),
            'themesActive' => Theme::find()->where(['status' => Theme::STATUS_ACTIVE])->count(),
        ];
    }
}