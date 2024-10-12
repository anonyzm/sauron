<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 29.10.19
 * Time: 13:07
 */

namespace common\components\instagram;


use common\exceptions\ParserWrongResultException;
use common\models\presentation\PresentationMention;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

class PresentationMentionConsister extends Component
{
    /** @var PresentationMention[] */
    protected $_models = [];

    protected $_requests = [];

    /** @var Service */
    protected $parser = null;

    public function init()
    {
        parent::init();

        // TODO: переделать все запросы тут через instaparser-lib
        $this->parser = new Service();
    }

    /**
     * @param PresentationMention $model
     */
    public function addModel(PresentationMention $model)
    {
        $this->_models[$model->external_id] = $model;
        $this->_requests[$model->external_id] = ['GET', 'media?code=' . $model->codeFromLink];
    }

    /**
     * @param array $models
     */
    public function setModels(array $models)
    {
        foreach ($models as $model) {
            $this->addModel($model);
        }
    }

    /**
     * @return bool|PresentationMention[]
     */
    public function batchUpdate()
    {
        if (empty($this->_requests)) {
            return false;
        }

        $doneCount = 0;
        $this->parser->batch($this->_requests, function (ResponseInterface $response) use (&$doneCount) {
            try {
                if($response->getStatusCode() !== 200) {
                    throw new ParserWrongResultException('Wrong status code: ' . $response->getStatusCode());
                }
                $json = json_decode($response->getBody(), true);
                if(!$json) {
                    throw new ParserWrongResultException('Json result is empty');
                }
                $external_id = ArrayHelper::getValue($json, 'data.id');
                if(!$external_id) {
                    throw new ErrorException('`external_id` is empty');
                }

                /** @var PresentationMention $model */
                $model = $this->_models[$external_id];

                $userpic = ArrayHelper::getValue($json, 'data.user.profile_pic_url');
                $username = ArrayHelper::getValue($json, 'data.user.full_name');
                $userlogin = ArrayHelper::getValue($json, 'data.user.username');
                $picture = ArrayHelper::getValue($json, 'data.images.standard_resolution.url');

                if (!empty($userpic)) {
                    $model->userpic = $userpic;
                }
                if (!empty($username)) {
                    $model->username = $username;
                }
                if (!empty($userlogin)) {
                    $model->userlogin = $userlogin;
                }
                if (!empty($picture)) {
                    $model->media = $picture;
                }

                // TODO: возможно вынести сам процесс сохранения в отдельный джоб, это сэкономит около 1-1.5 сек
                if ($model->save()) {
                    $doneCount++;
                }
                else {
                    \Yii::error([
                        'msg' => 'Instagram prepare feed - Error saving PresentationMention',
                        'errors' => json_encode($model->errors),
                        'attributes' => json_encode($model->attributes),
                    ]);
                }
            }
            catch(\Throwable $e) {
                \Yii::error([
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),

                ]);
            }
        }, 20);

        if(count($this->_requests) !== $doneCount) {
            \Yii::error([
                'msg' => 'Number of results doesn\'t match number of requests',
                'requests' => count($this->_requests),
                'results' => $doneCount,
            ]);
        }

        return $this->_models;
    }
}