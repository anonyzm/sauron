<?php
namespace operator\actions;

use common\components\youtube\Parser;
use common\components\youtube\Service;
use common\exceptions\ParserWrongResultException;
use common\models\transport\data\ScanData;
use common\models\transport\Entry;
use common\models\transport\payloads\SauronResultPayload;
use common\models\transport\responses\ScanResponse;
use operator\helpers\EntriesFilterHelper;
use Yii;

class YoutubeAction extends BaseAction
{
    /* @var Service */
    protected $service;

    /* @var Parser */
    protected $parser;

    /** @var ScanData $data */
    protected $data;

    public $isNeedMinusWords;

    // for instagram needed
    public $isNeedExtraPage;

    public $isMultiWord;


    public function init()
    {
        parent::init();
        $this->data = $this->request->data;
        $this->service = new Service();
        $this->parser = new Parser();
    }

    protected function multiWord(): string
    {
        $words = urlencode(implode('|', $this->data->words));
        $wordsPart = $words;
        if (!empty($this->data->minus_words)) {
            $minusWords = '';
            foreach ($this->data->minus_words as $minusWord) {
                $minusWords .= '-' . $minusWord . ' ';
            }
            $minusWords = trim($minusWords);
            $wordsPart .= ' ' . $minusWords;
        }
        return $wordsPart;
    }

    /**
     * @return SauronResultPayload
     * @throws ParserWrongResultException
     */
    protected function perform(): SauronResultPayload
    {
        $totalEntries = [];
        if ($this->isMultiWord) {
            $tag = $this->multiWord();
            $totalEntries = $this->loadPosts($tag);
        } else {
            foreach ($this->data->words as $word) {
                $entries = $this->loadPosts($word);
                $totalEntries = array_merge($totalEntries, $entries);
                if (!empty($this->data->limit) && count($totalEntries) >= $this->data->limit) {
                    break;
                }
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
        Yii::debug("mention total count added: " . count($totalEntries) . " for theme_id: {$this->data->theme_id}");

        $this->response->data = $responseData;
        return $this->response;
    }

    protected function loadPosts(string $tag): array
    {
        $nextUrl = null;
        $entries = [];

        do {
            $response = $this->service->loadPosts($tag, $nextUrl);
            Yii::debug('YOUTUBE RESPONSE');
            Yii::debug(print_r($response, true));
            if ($response) {
                $nextUrl = $this->parser->parseAfter($response);
                $newData = $this->parser->parsePosts($response);
                //нет объектов по тегу
                if (!$newData) {
                    Yii::debug('no new data break');
                    break;
                }
                if ($this->isNeedExtraPage) {
                    $newData = $this->extraPage($newData);

                    //нет объектов после фильрации, все старые
                    if (!$newData) {
                        Yii::debug('no new data after filter break');
                        break;
                    }

                    $entries = array_merge($entries, $newData);
                } else {
                    foreach ($newData as $entry) {
                        /** @var Entry $entry */
//                        Yii::debug("min time {$this->data->min_time} - entry created: {$entry->created}");
                        if ($this->data->min_time >= $entry->created) {
                            Yii::debug('min time break');
                            break 2;
                        } else {
                            $entries[] = $entry;
                        }
                    }
                }

            } else {
                Yii::debug('no response break');
                break;
            }
        } while ($nextUrl);


        if ($this->isNeedMinusWords) {
            $entries = $this->minusWords($entries);
        }

        Yii::debug("mention {$tag} count added: " . count($entries));
        return $entries;
    }

    protected function minusWords(array $entries): array
    {
        if ($this->data->minus_words) {
            return EntriesFilterHelper::minusWords($entries, $this->data->minus_words);
        } else {
            return $entries;
        }
    }

    protected function extraPage(array $entries): array
    {
        $newData = EntriesFilterHelper::minusOld($entries, $this->data->min_time);
        return $newData;
    }
}