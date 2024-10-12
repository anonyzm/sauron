<?php

namespace scheduler\console;

use common\console\BaseController;
use common\models\Theme;
use common\models\transport\data\ScanData;
use common\models\transport\payloads\SauronActionPayload;
use Yii;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class ScanController extends BaseController
{
    public function actionPerform()
    {
        $themes = Theme::find()->where(['status' => Theme::STATUS_ACTIVE])->orderBy(['scanned_at' => SORT_ASC]);
        foreach ($themes->each() as $theme) {
            /** @var Theme $theme */
            foreach ($theme->sources as $source) {
                // для всех источников кроме Youtube
                if($source === Theme::SOURCE_YOUTUBE) {
                    continue;
                }
                
                // для каждого источника создаем задачу
                /** @var Theme $theme */
                $message = \Yii::$app->rabbit->createMessage([
                    'body' => new SauronActionPayload([
                        'action' => $source,
                        'data' => new ScanData([
                            'theme_id' => $theme->id,
                            'words' => $theme->words,
                            'minus_words' => $theme->minusWords,
                            'min_time' => empty($theme->scanned_at) ? time() : $theme->scanned_at, // в первый раз собираем от текущего времени, чтоб не собирать из прошлого, до момента начала сбора
                            'limit' => $theme->allowedLimit,
                        ]),
                    ]),
                ]);

                \Yii::$app->rabbit->exchange(Yii::$app->params['operatorExchange'])
                    ->routingKey(Yii::$app->params['operatorRoutingKey'])
                    ->push($message);
            }
        }
    }
}