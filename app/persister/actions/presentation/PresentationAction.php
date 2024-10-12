<?php

namespace persister\actions\presentation;

use common\helpers\TimestampHelper;
use common\models\Account;
use common\models\Mention;
use common\models\presentation\MentionsDay;
use common\models\presentation\PresentationMention;
use common\models\Theme;
use common\models\transport\data\PresentationData;
use persister\actions\BaseAction;
use Yii;
use yii\helpers\ArrayHelper;

class PresentationAction extends BaseAction
{
    protected function perform()
    {
        /** @var PresentationData $data */
        $data = $this->request->data;

        $theme = Theme::findOne(['_id' => $data->theme_id]);
        $allowedLimit = $theme->allowedLimit;
        $from = time();
        if ($from % 86400 < 600) {
            $from -= 600;
        }
        $from = TimestampHelper::dayStart($from, $theme->account->timezone);
        $to = $from + 86400;

        $lastPresentationTimes = [];
        foreach ($theme->sources as $source) {
            $lastPresentationTime = 0;
            $lastPresentationMention = PresentationMention::find()->where(['theme_id' => $data->theme_id, 'source' => $source])->orderBy(['created' => SORT_DESC])->limit(1)->one();
            if ($lastPresentationMention) {
                $lastPresentationTime = $lastPresentationMention->created;
            }
            $lastPresentationTimes[$source] = $lastPresentationTime;
        }
        $mentionQuery = Mention::find()->where([
            'and',
            ['>=', 'created', $from],
            ['<', 'created', $to],
            ['=', 'theme_id', $data->theme_id],
        ]);

        // если лимит еще не истек
        if ($allowedLimit > 0) {
            $limitCursor = 0;
            $limitReached = false;
            foreach ($mentionQuery->batch() as $mentions) {
                $mentionsToInsert = [];
                foreach ($mentions as $mention) {
                    /** @var Mention $mention */
                    if (!isset($lastPresentationTimes[$mention->source])) {
                        continue;
                    } else if ($mention->created <= $lastPresentationTimes[$mention->source]) {
                        continue;
                    }

                    $presentationMention = PresentationMention::fromMention($mention);
                    if ($presentationMention) {
                        $attributes = $presentationMention->attributes;
                        unset($attributes['_id']);

                        $mentionsToInsert[] = $attributes;
                        $limitCursor++;
                    }

                    if ($limitCursor >= $allowedLimit) {
                        $limitReached = true;
                        break;
                    }
                }

                // записываем данные самих упоминаний
                if (count($mentionsToInsert)) {
                    $insertIds = Yii::$app->presentationMongodb->createCommand()->batchInsert(PresentationMention::collectionName(), $mentionsToInsert);
                    if (count($mentionsToInsert) !== count($insertIds)) {
                        \Yii::error([
                            'msg' => 'Number of rows doesn\'t match number of rows inserted! [PresentationAction]',
                            'toInsert' => json_encode($mentionsToInsert),
                            'insertIds' => json_encode($insertIds)
                        ]);
                    }
                }

                if ($limitReached) {
                    break;
                }
            }

            // считаем лимиты темы и удаляем persisted
            Yii::$app->mongodb->getCollection(Theme::collectionName())->update(['_id' => $theme->id], [
                '$inc' => [
                    'collected' => $limitCursor,
                    'limit' => (-1 * $limitCursor)
                ],
                '$set' => [
                    'persisted' => null
                ]
            ]);

            // считаем лимиты аккаунта
            Yii::$app->mongodb->getCollection(Account::collectionName())->update(['_id' => $theme->account_id], [
                '$inc' => [
                    'collected' => $limitCursor
                ]
            ]);
        }

        // группируем по дням и источникам
        $recalculateDays = ArrayHelper::getValue(Yii::$app->params, 'settings.presentationRecalculateDays', 2);
        $recalculateTime = strtotime(date('Y-m-d 00:00:00', strtotime("-{$recalculateDays} days")));
        $query = PresentationMention::find()->where(['>=', 'created', $recalculateTime])->andWhere(['theme_id' => $theme->id]);
        $mentionsSource = [];
        foreach ($query->each() as $mention) {
            /** @var Mention $mention */
            $dayTimestamp = TimestampHelper::dayStart($mention->created, $theme->account->timezone);
            $mentionsSource[$mention->source][$dayTimestamp] = isset($mentionsSource[$mention->source][$dayTimestamp]) ? ($mentionsSource[$mention->source][$dayTimestamp] + 1) : 1;
        }
        // записываем в группирующую таблицу
        foreach ($mentionsSource as $source => $mentionsDays) {
            foreach ($mentionsDays as $dayTimestamp => $count) {
                $mentionsDay = MentionsDay::findOne([
                    'day_timestamp' => $dayTimestamp,
                    'source' => $source,
                    'theme_id' => $theme->id
                ]);
                if (!$mentionsDay) {
                    $mentionsDay = new MentionsDay();
                    $mentionsDay->theme_id = $theme->id;
                    $mentionsDay->day_timestamp = $dayTimestamp;
                    $mentionsDay->source = $source;
                }
                $mentionsDay->count = $count;
                if (!$mentionsDay->save()) {
                    \Yii::error([
                        'msg' => 'Error updating MentionsDay count [PresentationAction]',
                        'errors' => json_encode($mentionsDay->errors),
                        'attributes' => json_encode($mentionsDay->attributes),
                    ]);
                }
            }
        }
    }
}