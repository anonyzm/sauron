<?php
namespace api\controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{
    public function actionIndex() {
        throw new NotFoundHttpException();
    }
}