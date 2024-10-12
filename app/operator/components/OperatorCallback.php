<?php

namespace operator\components;

use operator\actions\BaseAction;
use common\exceptions\ParserException;
use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
use ladno\woody\models\MQMessage\Message;
use ladno\woody\models\MQMessage\Error;

use Yii;
use yii\base\Model;

/**
 * Class OperatorCallback
 *
 * @package app\components
 */
class OperatorCallback
{
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
                throw new ParserException("Message is invalid\n" . json_encode($request->errors));
            }

            $className = 'operator\actions\\' . ucfirst($request->action) . 'Action';
            if (!class_exists($className)) {
                throw new ParserException("Action class does not exist: `{$className}`");
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
            $log = null;
            try {
                $log = json_encode($response->log);
            }
            catch (\Throwable $e) {
                $log = '[ERROR_PARSING_LOGS]';
            }

            \Yii::error([
                'msg' => 'Wrong action response',
                'action' => $request->action,
                'status' => $response->status,
                'message' => (string) $response->message,
                'error' => (string) $response->error,
                'data' => ($response->data instanceof Model) ? json_encode($response->data->attributes) : '[ERROR_PARSING_DATA]',
                'log' => $log,
            ]);
        }
        else {
            return $response;
        }
    }
}