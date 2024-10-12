<?php

namespace persister\components;

use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
use ladno\woody\models\MQMessage\Error;
use ladno\woody\models\MQMessage\Message;
use persister\actions\BaseAction;
use persister\exceptions\PersisterException;
use Yii;
use yii\base\ErrorException;

/**
 * Class PresentationCallback
 *
 * @package app\components
 */
abstract class BaseCallback
{
    /**
     * @return string
     */
    abstract public static function actionNamespace();

    /**
     * @return string
     * @throws \yii\base\ErrorException
     */
    public static function perform(Message $MQMessage)
    {
        /** @var SauronActionPayload $request */
        $request = $MQMessage->body;

        $response = new SauronResultPayload([
            'message' => $MQMessage,
        ]);

        try {
            if (!$request->validate()) {
                throw new PersisterException("Message is invalid\n" . json_encode($request->errors));
            }

            $className = static::actionNamespace() . '\\' . ucfirst($request->action) . 'Action';
            if (!class_exists($className)) {
                throw new PersisterException("Action class does not exist: `{$className}`");
            }

            /** @var $action BaseAction; */
            $action = new $className([
                'request' => $request,
                'response' => $response,
            ]);
            $action->handle();

        } catch (\Throwable $e) {
            $response->status = 'fail';
            $response->error = new Error([
                'message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Yii::error($e);
        }

        if (!in_array($response->status, ['ok', 'fail'])) {
            Yii::warning("Status message is not set correctly");
            $response->status = ($response->message instanceof Error) ? 'fail' : 'ok';
        }

        if(!$response->isOk()) {
            Yii::error($response, 'ERROR_RESPONSE');
        }
        else {
            return $response;
        }
    }
}