<?php
namespace operator\actions;

use common\components\youtube\Parser;
use common\components\youtube\Service;
use common\exceptions\ParserWrongResultException;
use common\models\transport\data\UpdateData;
use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
use common\models\transport\responses\UpdateResponse;
use Yii;
use yii\base\BaseObject;

class YoutubeUpdateAction extends BaseObject
{
    /** @var SauronActionPayload */
    public $request;

    /** @var SauronResultPayload */
    public $response;

    /* @var Service */
    protected $service;

    /* @var Parser */
    protected $parser;

    /** @var UpdateData $data */
    protected $data;

    public function init()
    {
        parent::init();
        $this->data = $this->request->data;
        $this->service = new Service();
        $this->parser = new Parser();
    }

    /** @return void */
    public function handle()
    {
        try {
            $this->perform();
            if (empty($this->response->status)) {
                $this->response->status = 'ok';
            }

            if($this->response->isOk() && $this->response->data instanceof UpdateResponse) {
                $message = \Yii::$app->rabbit->createMessage([
                    'body' => new SauronActionPayload([
                        'action' => 'repersist',
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

    protected function perform(): SauronResultPayload
    {
        $postsResponse = $this->service->getPostsByIds($this->data->ids);
        $entries = $this->parser->parsePosts($postsResponse);

        $responseData = new UpdateResponse();
        $responseData->setAttributes([
            'entries' => $entries,
        ]);
        if (!$responseData->validate()) {
            throw new ParserWrongResultException('Error validating result data: ' . json_encode($responseData->errors));
        }
        Yii::debug("mention total count to update: " . count($entries));

        $this->response->data = $responseData;
        return $this->response;
    }
}