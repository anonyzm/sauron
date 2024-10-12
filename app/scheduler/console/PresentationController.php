<?php

namespace scheduler\console;

use common\console\BaseController;
use common\models\Theme;
use common\models\transport\data\PresentationData;
use common\models\transport\payloads\SauronActionPayload;
use Yii;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class PresentationController extends BaseController
{
    public function actionPerform()
    {
        // выбираем только активные темы
        $themes = Theme::find()->where(['persisted' => ['$ne' => null]])->andWhere(['status' => Theme::STATUS_ACTIVE])->orderBy(['persisted' => SORT_ASC]);
        foreach ($themes->each() as $theme) {
            /** @var Theme $theme */
            // сообщаем персистеру о том, что можно перекладывать
            $message = Yii::$app->rabbit->createMessage([
                'body' => new SauronActionPayload([
                    'action' => 'presentation',
                    'data' => new PresentationData([
                        'theme_id' => $theme->id,
                    ]),
                ]),
            ]);
            Yii::$app->rabbit->exchange(Yii::$app->params['presentationExchange'])
                ->routingKey(Yii::$app->params['presentationRoutingKey'])
                ->push($message);
        }
    }
}