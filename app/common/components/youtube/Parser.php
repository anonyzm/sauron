<?php


namespace common\components\youtube;


use common\interfaces\ParserInterface;
use common\models\transport\Entry;
use Google_Service_YouTube_Video;
use Yii;

class Parser implements ParserInterface
{
    /**
     * @param $decodedBody
     * @return Entry[]
     */
    public function parsePosts($decodedBody)
    {
        $entries = [];
        /** @var Google_Service_YouTube_Video $post */
        foreach ($decodedBody['items'] as $post) {
            $entry = new Entry();
            $entry->setAttributes([
                'external_id' => $post->getId(),
                'link' => 'https://www.youtube.com/watch?v=' . $post->getId(),
                'user_id' => $post->getSnippet()->getChannelId(),
                'username' => $post->getSnippet()->getChannelTitle(),
                'userlogin' => $post->getSnippet()->getChannelId(),
                'title' => $post->getSnippet()->getTitle(),
                'text' => $post->getSnippet()->getDescription(),
                'media' => $post->getSnippet()->getThumbnails()->getHigh()->url,
                'source' => 'youtube',
                'meta' => [
                    'comments' => $post->getStatistics()->getCommentCount(),
                    'likes' => $post->getStatistics()->getLikeCount(),
                    'dislike' => $post->getStatistics()->getDislikeCount(),
                    'views' => $post->getStatistics()->getViewCount(),
                ],
                'created' => strtotime($post->getSnippet()->getPublishedAt())
            ]);
            if (!$entry->validate()) {
                Yii::error([
                    'msg' => 'Error validating Entry',
                    'data' => json_encode($entry->attributes),
                    'errors' => json_encode($entry->errors)
                ]);
                continue;
            }

            $entries[] = $entry;
        }
        return $entries;
    }

    public function parseAfter($decodedBody)
    {
        return $decodedBody['nextPageToken'];
    }
}