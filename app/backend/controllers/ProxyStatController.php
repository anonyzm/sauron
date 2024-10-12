<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * ThemeController implements the CRUD actions for Theme model.
 */
class ProxyStatController extends \ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\controllers\BackendProxyController
{
    public $layout = '@backend/views/layouts/main';

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
}
