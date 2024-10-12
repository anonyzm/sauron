<?php

namespace persister\console;

use common\models\Service;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class AppController extends \common\console\BaseController
{
    public function actionInit() {
        $this->run('migrate/up', ['interactive' => $this->interactive]);
        $this->run('app/create-service', [
            ArrayHelper::getValue(Yii::$app->params, 'settings.defaultService'),
            ArrayHelper::getValue(Yii::$app->params, 'settings.defaultServiceKey'),
        ]);
    }

    /**
     * Создание сервиса
     * @param $alias
     */
    public function actionCreateService($alias = 'default', $token = null) {
        $service = Service::find()->where(['alias' => $alias])->one();
        if(!$service) {
            $service = new Service();
            $service->alias = $alias;
            if(!is_null($token)) {
                $service->token = $token;
            }
            if (!$service->save()) {
                Console::output('Error saving service: ' . json_encode($service->errors));
            } else {
                Console::output("Service #{$service->id} created!");
                Console::output("Alias: {$service->alias}");
                Console::output("Token: {$service->token}");
            }
        }
        else {
            Console::output("Service already created #{$service->id}");
            Console::output("Alias: {$service->alias}");
            Console::output("Token: {$service->token}");
        }
    }

    /**
     * Обновление токена сервиса
     * @param $alias
     */
    public function actionUpdateServiceToken($alias) {
        $service = Service::findOne(['alias' => $alias]);
        $service->generateToken();
        if(!$service->save()) {
            Console::output('Error saving service: '.json_encode($service->errors));
        }
        else {
            Console::output("Service #{$service->id} updated!");
            Console::output("Alias: {$service->alias}");
            Console::output("Token: {$service->token}");
        }
    }

    /**
     * Удаление сервиса
     * @param $alias
     */
    public function actionDeleteService($alias) {
        Service::deleteAll(['alias' => $alias]);
        Console::output("Service deleted!");
    }

    /**
     * Список сервисов
     */
    public function actionListServices() {
        $services = Service::find()->all();
        foreach ($services as $service) {
            /** @var Service $service */
            Console::output("ID: {$service->id}");
            Console::output("Alias: {$service->alias}");
            Console::output("Token: {$service->token}");
            Console::output('Created: ' . date('d.m.y H:i', $service->created_at));
            Console::output('Updated: ' . date('d.m.y H:i', $service->updated_at));
            Console::output('-----------------------------');
        }
    }
}
