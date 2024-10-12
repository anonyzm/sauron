<?php

namespace persister\actions\presentation;

use common\helpers\TimestampHelper;
use common\models\Mention;
use common\models\presentation\MentionsDay;
use common\models\presentation\PresentationMention;
use common\models\transport\data\TimezoneData;
use persister\actions\BaseAction;
use Yii;

class TimezoneAction extends BaseAction
{
    protected function perform()
    {
        /** @var TimezoneData $data */
        $data = $this->request->data;

        $mentionQuery = PresentationMention::find()->where(['theme_id' => $data->theme_id])->orderBy(['created' => SORT_ASC]);
        $mentionsSources = [];
        foreach ($mentionQuery->each(300) as $mention) {
            /** @var Mention $mention */
            $dayTimestamp = TimestampHelper::dayStart($mention->created, $data->timezone);
            $mentionsSources[$mention->source][$dayTimestamp] = isset($mentionsSources[$mention->source][$dayTimestamp]) ? ($mentionsSources[$mention->source][$dayTimestamp] + 1) : 1;
        }

        // удаляем все данные графиков
        MentionsDay::deleteAll(['theme_id' => $data->theme_id]);
        // заново заполяняем
        $mentionsToInsert = [];
        foreach($mentionsSources as $source => $mentionsDays) {
            foreach ($mentionsDays as $dayTimestamp => $mentionsDay) {
                $md = new MentionsDay();
                $md->setAttributes([
                    'theme_id' => $data->theme_id,
                    'day_timestamp' => $dayTimestamp,
                    'count' => $mentionsDay,
                    'source' => $source,
                ]);
                if ($md->validate()) {
                    $mentionToInsert = $md->attributes;
                    unset($mentionToInsert['_id']);
                    $mentionsToInsert[] = $mentionToInsert;
                } else {
                    Yii::error([
                        'msg' => 'Error validating MentionsDay [TimezoneAction]',
                        'errors' => json_encode($md->errors),
                    ]);
                }
            }
        }

        $insertIds = Yii::$app->presentationMongodb->createCommand()->batchInsert(MentionsDay::collectionName(), $mentionsToInsert);
        if (count($mentionsToInsert) !== count($insertIds)) {
            \Yii::error([
                'msg' => 'Number of rows doesn\'t match number of rows inserted! [TimezoneAction]',
                'toInsert' => json_encode($mentionsToInsert),
                'insertIds' => json_encode($insertIds)
            ]);
        }
    }
}