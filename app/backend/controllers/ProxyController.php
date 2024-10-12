<?php

namespace backend\controllers;

use backend\models\ProxyUpdateForm;
use common\helpers\FineproxyHelper;
use ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\Proxy;
use ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\ProxyHit;
use ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\ProxyPool;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * ThemeController implements the CRUD actions for Theme model.
 */
class ProxyController extends Controller
{
    public $layout = '@backend/views/layouts/main';

    public function  init()
    {
        parent::init();

        ProxyPool::db('mongodb');
        ProxyHit::db('mongodb');
        Proxy::db('mongodb');
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function getViewPath()
    {
        return Yii::getAlias('@backend/views/proxy');
    }

    /**
     * Lists all Theme models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new ProxyUpdateForm();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $result = FineproxyHelper::updateProxies($model->pool_id);
            return $this->render('update', [
                'result' => $result,
            ]);
        }

        return $this->render('index', [
            'model' => $model
        ]);
    }
}
