<?php

namespace persister\actions\persister;

use common\models\Mention;
use common\models\Theme;
use common\models\transport\Entry;
use common\models\transport\responses\ScanResponse;
use persister\actions\BaseAction;
use Yii;

class PersistAction extends BaseAction
{
    protected function perform()
    {
        /** @var ScanResponse $data */
        $data = $this->request->data;
        foreach ($data->entries as $entry) {
            try {
                // TODO: переделать на batchInsert с игнорированием дубликатов
                /** @var Entry $entry */
                $mention = new Mention();
                $mention->setAttributes([
                    'theme_id' => $data->theme_id,
                    'external_id' => $entry->external_id,
                    'link' => $entry->link,
                    'media' => $entry->media,
                    'media_type' => $entry->media_type,
                    'user_id' => $entry->user_id,
                    'username' => $entry->username,
                    'userlogin' => $entry->userlogin,
                    'userpic' => $entry->userpic,
                    'title' => $entry->title,
                    'text' => $entry->text,
                    'external_link' => $entry->external_link,
                    'source' => $entry->source,
                    'created' => intval($entry->created),
                    'meta' => $entry->meta,
                ]);

                if (!$mention->save()) {
                    Yii::error([
                        'msg' => 'Error saving Mention',
                        'errors' => json_encode($mention->errors)
                    ]);
                }
            } catch (\Throwable $e) {
                // игнорим ошибки на запись дубликатов
                //\Yii::error($e);
            }
        }

        $theme = Theme::findOne(['_id' => $data->theme_id]);
        if(!$theme) {
            Yii::error([
                'msg' => 'Theme not found #' . $data->theme_id,
            ]);
        }
        $theme->persisted = time();
        if(!$theme->save()) {
            Yii::error([
                'msg' => 'Error saving theme #' . $data->theme_id,
                'errors' => json_encode($theme->errors)
            ]);
        }
    }
}