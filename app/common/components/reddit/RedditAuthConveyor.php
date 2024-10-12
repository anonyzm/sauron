<?php
namespace common\components\reddit;

use common\models\auth\RedditUser;
use yii\base\Component;
use yii\httpclient\Client;

/**
 * Class ProxyConveyor
 * @package ladno\instaparserlib\httpclient\proxy
 * @property-read Client $client
 */
class RedditAuthConveyor extends Component
{
    /**
     * @var string
     */
    public $apiUrl;

    /**
     * @var string
     */
    public $authToken;

    /**
     * @var Client
     */
    protected $_client = null;

    protected function getClient() {
        if(is_null($this->_client)) {
            $this->_client = new Client();
        }

        return $this->_client;
    }

    public function hit(RedditUser $redditUser): bool {
        $request = $this->client->createRequest()
            ->setMethod('POST')
            ->setUrl("{$this->apiUrl}/hit?token={$this->authToken}")
            ->setData([
                'redditUser' => $redditUser->attributes,
            ])->send();

        if($request->getStatusCode() !== 200) {
            return false;
        }
        return true;
    } 

    public function get(): RedditUser {
        $request = $this->client->createRequest()
            ->setMethod('GET')
            ->setUrl("{$this->apiUrl}/get?token={$this->authToken}")
            ->send();
        $data = $request->getData();

        $redditUser = RedditUser::instantiate($data);
        RedditUser::populateRecord($redditUser, $data);
        $redditUser->afterFind();

        return $redditUser;
    }
}