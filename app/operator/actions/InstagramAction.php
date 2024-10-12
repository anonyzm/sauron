<?php

namespace operator\actions;

use common\exceptions\ParserWrongResultException;
use ladno\instaparserlib\models\ApiResponse;
use ladno\instaparserlib\models\IgPost;
use common\models\transport\data\ScanData;
use common\models\transport\Entry;
use common\models\transport\responses\ScanResponse;
use common\models\transport\payloads\SauronResultPayload;
use Yii;

class InstagramAction extends BaseAction
{
    const REQUEST_COUNT = 20;
    const MAXIMUM_ITERATIONS = 20;

    private $data;

    public function init()
    {
        parent::init();
        /** @var ScanData $data */
        $this->data = $this->request->data;
    }

    protected function perform(): SauronResultPayload
    {
        $totalEntries = [];

        foreach ($this->data->words as $word) {
            // принято решение в рамках LOL-26 если есть в слове пробел - его удаляем иначе будет ошибочный ответ от инсты, согласовано с ДВ
            $word = str_replace(' ', '', $word);

            $entries = $this->loadMedia($word);
            $totalEntries = array_merge($totalEntries, $entries) ;

            if (!empty($this->data->limit) && count($totalEntries) >= $this->data->limit) {
                // обрезаем все что выше лимита
                $totalEntries = array_slice($totalEntries, 0, $this->data->limit, true);
                break;
            }
        }
        $responseData = new ScanResponse();
        $responseData->setAttributes([
            'theme_id' => $this->data->theme_id,
            'entries' => $totalEntries,
        ]);
        if (!$responseData->validate()) {
            throw new ParserWrongResultException('Error validating result data: ' . json_encode($responseData->errors));
        }
        Yii::debug("instagram mention total count added: " . count($totalEntries) . " for theme_id: {$this->data->theme_id}");

        $this->response->data = $responseData;
        return $this->response;
    }

    protected function loadMedia(string $tag): array
    {
        $after = null;
        $entries = [];
        $i = 0;

        do {
            /** @var ApiResponse $response */
            $response = Yii::$app->instaparser->getTagMedia($tag, self::REQUEST_COUNT, $after);
            if ($response && $response->data) {
                $newData = $this->parseMedia($response->data);
                $after = $response->paging->next ?? null;

                //нет объектов по тегу
                if (!$newData) {
                    Yii::debug('no new data break');
                    break;
                }
                $newData = $this->minusOld($newData, $this->data->min_time);

                //нет объектов после фильрации, все старые
                if (!$newData) {
                    Yii::debug('no new data after filter break');
                    break;
                }

                $entries = array_merge($entries, $newData);
            } else {
                Yii::debug('no response break');
                break;
            }
            $i++;
        } while ($after && ($i < self::MAXIMUM_ITERATIONS));
        if ($this->data->minus_words) {
            $entries = $this->minusWords($entries, $this->data->minus_words);
        }
        Yii::debug("instagram mention {$tag} count added: " . count($entries));
        return $entries;
    }

    public function parseMedia(array $posts): array
    {
        $entries = [];
        foreach ($posts as $post) {
            /** @var IgPost $post */
            $entry = new Entry();
            $entry->setAttributes([
                'external_id' => $post->id,
                'link' => $post->link,
                'user_id' => $post->user->id,
                'text' => $post->caption->text,
                'source' => 'instagram',
                'meta' => [
                    'likes' => $post->likes->count ?? 0,
                    'comments' => $post->comments->count ?? 0
                ],
                'created' => $post->created_time,
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

    protected function minusWords(array $entries, array $minusWords): array
    {
        $filteredEntries = [];
        foreach ($entries as $entry) {
            if ($entry->text) {
                foreach ($minusWords as $minusWord) {
                    $re = "/#?({$minusWord})/mi";
                    if (!preg_match_all($re, $entry->text, $matches, PREG_SET_ORDER, 0)) {
                        $filteredEntries[] = $entry;
                    }
                }
            }
        }
        return $filteredEntries;
    }

    protected function minusOld(array $entries, int $minTime): array
    {
        $filteredEntries = [];
        foreach ($entries as $entry) {
            if ($minTime < $entry->created) {
                $filteredEntries[] = $entry;
            }
        }
        return $filteredEntries;
    }

}