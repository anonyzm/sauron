<?php
namespace common\components\reddit;

use common\models\transport\Entry;
use Yii;
use yii\helpers\ArrayHelper;

class Parser
{
    public function parsePosts($decodedBody): array
    {
        $entries = [];
        $children = $decodedBody['data']['children'];
        if (!$children) {
            return $entries;
        }
        foreach ($children as $post) {
            $post = $post['data'];
            $media = ArrayHelper::getValue($post, 'preview.images.0.source.url');
            if(!empty($media)) {
                if ($post['thumbnail'] === 'nsfw') { // 18+
                    $media = 'nsfw';
                }
                else {
                    $media = str_replace('&amp;', '&', $media);
                }
            }
            $text = $post['selftext'] ?? null;
            if(!is_null($text)) {
                $text = str_replace(['&amp;#x200B;'], '', $text);
            }

            $entry = new Entry();
            $entry->setAttributes([
                'external_id' => $post['id'],
                'link' => $post['permalink'],
                'user_id' => $post['author_fullname'],
                'username' => $post['author'],
                'userlogin' => $post['subreddit_name_prefixed'],
                'media' => $media,
                'source' => 'reddit',
                'title' => $post['title'],
                'text' => $text,
                'external_link' => $post['url'],
                'meta' => [
                    'ups' => $post['ups'] ?? 0,
                    'comments' => $post['num_comments'] ?? 0
                ],
                'created' => $post['created_utc']
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

    public function parseAfter($decodedBody): ?string
    {
        return $decodedBody['data']['after'];
    }
}