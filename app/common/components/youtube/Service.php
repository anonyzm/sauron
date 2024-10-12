<?php

namespace common\components\youtube;

use common\helpers\TimestampHelper;
use common\interfaces\ServiceInterface;
use Google_Client;
use Google_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_SearchResult;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class Service extends BaseObject implements ServiceInterface
{
    /**
     * @var Google_Service_YouTube
     */
    private $youtube;

    public $publishedAfter;

    public function init()
    {
        parent::init();
        $client = new Google_Client();
        $client->setDeveloperKey(ArrayHelper::getValue(Yii::$app->params, 'youtube.key'));
        $this->youtube = new Google_Service_YouTube($client);
    }

    /**
     * @param string $tag
     * @param null|string $after
     * @return array|null
     */
    public function loadPosts(string $tag, ?string $after = ''): ?array
    {
        Yii::debug("tag: {$tag} after: {$after}");
        $listResponse = $this->youtube->search->listSearch('id', [
            'q' => $tag,
            'order' => 'date',
            'type' => 'video',
            'maxResults' => 50,
            'publishedAfter' => TimestampHelper::rfc3339($this->publishedAfter),
            'pageToken' => $after
        ]);
        $videoIds = [];
        /** @var Google_Service_YouTube_SearchResult $item */
        foreach ($listResponse->getItems() as $item) {
            $videoIds[] = $item->getId()->getVideoId();
        }
        $response = $this->youtube->videos->listVideos('id,snippet,player,statistics',
            ['id' => implode(',', $videoIds)]
        );
        $data['nextPageToken'] = $listResponse->nextPageToken;
        $data['items'] = $response->getItems();

        return $data;
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function getPostsByIds(array $ids) {
        $response = $this->youtube->videos->listVideos('id,snippet,player,statistics',
            ['id' => implode(',', $ids)]
        );
        $data['items'] = $response->getItems();
        return $data;
    }

    private function listVideos($part, $optParams = array()): ?ResponseInterface
    {
        Yii::debug("list videos");

        $params = array('part' => $part);
        $params = array_merge($params, $optParams);
        try {
            return $this->youtube->videos->call('list', array($params));
        } catch (Google_Exception $e) {
            Yii::error($e);
            return null;
        }
    }
}