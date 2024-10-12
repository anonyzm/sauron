<?php

namespace persister\actions\persister;

use common\models\Mention;
use common\models\transport\Entry;
use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\responses\UpdateResponse;
use persister\actions\BaseAction;
use Yii;

class RepersistAction extends BaseAction
{
    protected function perform()
    {
        /** @var UpdateResponse $data */
        $data = $this->request->data;
        foreach ($data->entries as $entry) {
            try {
                /** @var Entry $entry */
                $mentions = Mention::find()->where([
                    'external_id' => $entry->external_id,
                    'source' => $entry->source,
                ])->all();
                foreach ($mentions as $mention) {
                    /** @var Mention $mention */
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
                            'msg' => 'Error saving Mention',
                            'errors' => json_encode($mention->errors)
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Yii::error($e);
            }
        }

        $message = Yii::$app->rabbit->createMessage([
            'body' => new SauronActionPayload([
                'action' => 'representation',
                'data' => $data,
            ]),
        ]);
        Yii::$app->rabbit->exchange(Yii::$app->params['presentationExchange'])
            ->routingKey(Yii::$app->params['presentationRoutingKey'])
            ->push($message);
    }
}