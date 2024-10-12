<?php

namespace scheduler\console;

use common\console\BaseController;
use common\helpers\FineproxyHelper;
use Yii;
use yii\helpers\Console;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class ProxyController extends BaseController
{
    public function actionWarn() {
        Yii::$app->slack->send('@leonid время проверить прокси!', null, [], 'sauron-proxy');
    }

    public function actionUpdate($pool_id)
    {
        FineproxyHelper::updateProxies($pool_id);
    }

    public function actionTt() {
        Console::output(Inflector::camel2id(StringHelper::basename(get_called_class())));
    }
}