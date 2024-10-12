<?php

namespace persister\actions;

use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
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

    /** @return void */
    public function handle()
    {
        try {
            $this->perform();
            if (empty($this->response->status)) {
                $this->response->status = 'ok';
            }
        } catch (\Throwable $e) {
            //$this->log($e);
            throw $e;
        }
    }

    abstract protected function perform();
}