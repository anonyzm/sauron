<?php


namespace common\components\instagram;


use common\models\transport\Entry;
use Yii;

/**
 * Class Parser
 * @package common\components\instagram
 * @deprecated все общение с инстой через instaparser-lib
 */
class Parser
{

    public function parseMedia(array $decodedBody): array
    {
        $entries = [];
        if (!$decodedBody['data']) {
            return $entries;
        }
        foreach ($decodedBody['data'] as $post) {
            $entry = new Entry();
            $entry->setAttributes([
                'external_id' => $post['id'],
                'link' => $post['link'],
                'user_id' => $post['user']['id'],
                'text' => $post['caption']['text'] ?? null,
                'source' => 'instagram',
                'meta' => [
                    'likes' => $post['likes']['count'] ?? 0,
                    'comments' => $post['comments']['count'] ?? 0
                ],
                'created' => $post['created_time']
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

    public function parseNextUrl(array $decodedBody): ?string
    {
        $nextUrl = $decodedBody['paging']['next'] ?? null;
        Yii::debug("next url {$nextUrl}");
        return $nextUrl;
    }
}