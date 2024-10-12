<?php

namespace api\controllers;

use common\models\auth\RedditUser;
use yii\base\ErrorException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

class RedditAuthController extends Controller
{
    public $authToken;

    public function init()
    {
        parent::init();

        $token = \Yii::$app->request->get('token');
        if ($token !== $this->authToken) {
            throw new ForbiddenHttpException('Auth token is incorrect');
        }
    }

    /**
     * TODO: Сделать запрос на hit асинхронным
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionHit()
    {
        $data = \Yii::$app->request->post();
        $id = ArrayHelper::getValue($data, 'redditUser._id.$oid');
        $redditUser = RedditUser::findOne($id);
        if(!$redditUser) {
            \Yii::error([
                'msg' => 'Reddit user not found',
                'attributes' => $redditUser->attributes,
            ]);
            return;
        }

        $data = ArrayHelper::getValue($data, 'redditUser', []);
        unset($data['_id']);
        $redditUser->setAttributes($data);
        if (!$redditUser->save()) {
            throw new InvalidParamException('Wrong reddit user data: ' . json_encode($redditUser->errors));
        }
    }

    /**
     * @return array
     * @throws ErrorException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\mongodb\Exception
     */
    public function actionGet()
    {
        $redditUser = RedditUser::find()->where(['status' => RedditUser::STATUS_ACTIVE])->orderBy(['updated_at' => SORT_ASC])->one();
        return $redditUser->attributes;
    }
}