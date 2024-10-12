<?php

namespace persister\actions\presentation;

use common\models\presentation\PresentationMention;
use common\models\transport\Entry;
use common\models\transport\responses\UpdateResponse;
use persister\actions\BaseAction;
use Yii;

class RepresentationAction extends BaseAction
{
    protected function perform()
    {
        /** @var UpdateResponse $data */
        $data = $this->request->data;
        foreach ($data->entries as $entry) {
            try {
                /** @var Entry $entry */
                $mentions = PresentationMention::find()->where([
                    'external_id' => $entry->external_id,
                    'source' => $entry->source,
                ])->all();
                foreach ($mentions as $mention) {
                    /** @var PresentationMention $mention */
                    $mention->setAttributes([
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
                        'meta' => $entry->meta,
                    ]);

                    if (!$mention->save()) {
                        Yii::error([
                            'msg' => 'Error saving PresentationMention',
                            'errors' => json_encode($mention->errors)
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Yii::error($e);
            }
        }
    }
}