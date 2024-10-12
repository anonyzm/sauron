<?php

namespace operator\actions;

use common\models\Theme;
use common\models\transport\responses\ScanResponse;
use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
use Yii;
use yii\base\BaseObject;

/**
 * Class BaseAction
 *
 * @package app\actions
 */
abstract class BaseAction extends BaseObject
{
    /** @var SauronActionPayload */
    public $request;

    /** @var SauronResultPayload */
    public $response;

    public $responseAction = 'persist';

    /** @return void */
    public function handle()
    {
        try {
            $this->perform();
            if (empty($this->response->status)) {
                $this->response->status = 'ok';
            }

            // если успешно выполнили сканирование, то проставим `scanned_at` для темы
            if($this->response->isOk() && $this->response->data instanceof ScanResponse) {
                //TODO: заменить это на отправку сообщения в отдельную очередь обработки результатов сбора
                $theme = Theme::findOne(['_id' => $this->response->data->theme_id]);
                $theme->touch('scanned_at');
                if(!$theme->save()) {
                    \Yii::error([
                        'msg' => "Error changing theme's `scanned_at`",
                        'errors' => json_encode($theme->errors),
                    ]);
                }

                $message = \Yii::$app->rabbit->createMessage([
                    'body' => new SauronActionPayload([
                        'action' => $this->responseAction,
                        'data' => $this->response->data,
                    ]),
                ]);

                \Yii::$app->rabbit->exchange(Yii::$app->params['persisterExchange'])
                    ->routingKey(Yii::$app->params['persisterRoutingKey'])
                    ->push($message);
            }
        } catch (\Throwable $e) {
            //$this->log($e);
            throw $e;
        }
    }

    /** @return SauronResultPayload */
    abstract protected function perform();
}