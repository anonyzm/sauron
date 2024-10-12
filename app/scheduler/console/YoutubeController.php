<?php

namespace scheduler\console;

use common\console\BaseController;
use common\models\Mention;
use common\models\Theme;
use common\models\transport\data\ScanData;
use common\models\transport\data\UpdateData;
use common\models\transport\payloads\SauronActionPayload;
use Yii;

/**
 * Application setup and configuration
 *
 * @package console\controllers
 */
class YoutubeController extends BaseController
{
    const SCAN_PERIOD = 3600;

    public function actionScan()
    {
        $themes = Theme::find()->where(['status' => Theme::STATUS_ACTIVE])->orderBy(['scanned_at' => SORT_ASC]);
        foreach ($themes->each() as $theme) {
            /** @var Theme $theme */
            foreach ($theme->sources as $source) {
                // выполняем скан только для ютуба
                if($source === Theme::SOURCE_YOUTUBE) {
                    $scanFrom = ($theme->created_at > (time() - self::SCAN_PERIOD)) ? $theme->created_at : (time() - self::SCAN_PERIOD);
                    $message = \Yii::$app->rabbit->createMessage([
                        'body' => new SauronActionPayload([
                            'action' => Theme::SOURCE_YOUTUBE,
                            'data' => new ScanData([
                                'theme_id' => $theme->id,
                                'words' => $theme->words,
                                'minus_words' => $theme->minusWords,
                                'min_time' => $scanFrom,
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

    public function actionUpdate()
    {
        if (!Yii::$app->settings->get('youtube.updateEnabled')) {
            return;
        }

        $now = time();
        $mentionsQuery = Mention::find()
            ->where(['source' => Mention::SOURCE_YOUTUBE])
            ->andWhere(['<', 'updated_at', $now - Yii::$app->settings->get('youtube.updateTo')])
            ->andWhere(['>', 'updated_at', $now - Yii::$app->settings->get('youtube.updateFrom')])
            ->orderBy(['updated_at' => SORT_ASC]);
        foreach ($mentionsQuery->batch(Yii::$app->settings->get('youtube.updateBatchSize')) as $mentions) {
            $mentionIds = [];
            foreach ($mentions as $mention) {
                $mentionIds[] = $mention->external_id;
            }

            // отправляем задание в очередь
            $message = \Yii::$app->rabbit->createMessage([
                'body' => new SauronActionPayload([
                    'action' => 'youtubeUpdate',
                    'data' => new UpdateData([
                        'ids' => $mentionIds,
                    ]),
                ]),
            ]);
            \Yii::$app->rabbit->exchange(Yii::$app->params['operatorExchange'])
                ->routingKey(Yii::$app->params['operatorRoutingKey'])
                ->push($message);
        }
    }
}