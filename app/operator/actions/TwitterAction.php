<?php

namespace operator\actions;

use GuzzleHttp\Client;
use common\exceptions\ParserException;
use common\exceptions\ParserWrongResultException;
use ladno\proxyconveyor\models\ProxyHit;
use operator\helpers\GuzzleHelper;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use common\models\transport\data\ScanData;
use common\models\transport\Entry;
use common\models\transport\responses\ScanResponse;
use common\models\transport\payloads\SauronResultPayload;
use PHPHtmlParser\Dom;
use Psr\Http\Message\ResponseInterface;
use yii\helpers\ArrayHelper;

/**
 * Class ScanAction
 * @property-read Dom $domParser
 * @property-read Client $client
 *
 * @package app\actions
 */
class TwitterAction extends BaseAction
{
    const MAX_ITERATIONS = 50;
    const RETRY_COUNT = 5;

    const BASE_URI = 'https://twitter.com/';
    const API_URI = 'https://api.twitter.com/2/';
    const MOBILE_URI = 'https://mobile.twitter.com/';
    const GUEST_BEARER = 'AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';
    const BAD_HTTP_STATUSES = [
        401,
        403,
        429,
        502,
        503,
        504,
        522,
        523,
        524,
    ];

    /** @var Client */
    protected $_client = null;
    /** @var array */
    protected $_clientOptions = [
        'headers' => [
            //'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'upgrade-insecure-requests' => 1,
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
        ],
    ];

    protected $_domParser = null;

    public function getDomParser() {
        if(is_null($this->_domParser)) {
            $this->_domParser = new Dom();
        }

        return $this->_domParser;
    }

    public function getClient() {
        if(is_null($this->_client)) {
            $this->_client = new Client([
                'base_uri' => self::BASE_URI,
                'allow_redirects' => [
                    'max' => 8, // Allow up to eight redirects (that's plenty).
                ],
                'connect_timeout' => 30.0, // Give up trying to connect after 30s.
                'decode_content' => true, // Decode gzip/deflate/etc HTTP responses.
                'timeout' => 60.0, // Maximum per-request time (seconds).
                // Tells Guzzle to stop throwing exceptions on non-"2xx" HTTP codes,
                // thus ensuring that it only triggers exceptions on socket errors!
                // We'll instead MANUALLY be throwing on certain other HTTP codes.
                'http_errors' => false,
            ]);
        }

        return $this->_client;
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, $options = []) {
        $response = null;
        $attempt = 1;
        do {
            try {
                $proxy = \Yii::$app->twitterProxy->get();
                $options = ArrayHelper::merge([
                    'proxy' => (string) $proxy,
                ], $options);
                $microtime = microtime(true);
                $options = ArrayHelper::merge($this->_clientOptions, $options);
                $response = $this->client->request($method, $uri, $options);
                $statusCode = $response->getStatusCode();

                $proxyHit = new ProxyHit();
                $proxyHit->setAttributes([
                    'microtime' => microtime(true) - $microtime,
                    'url' => $uri,
                    'status' => $statusCode,
                    'failed' => 0,
                ]);

                if ($statusCode !== 200) {
                    if (in_array($statusCode, self::BAD_HTTP_STATUSES)) {
                        $proxyHit->failed = 1;
                    }
                    \Yii::$app->twitterProxy->hit($proxy, $proxyHit);

                    throw new ParserWrongResultException('Twitter: wrong response status code', [
                        'msg' => 'Wrong response status code',
                        'uri' => $uri,
                        'code' => json_encode($options),
                        'body' => $response->getBody(),
                    ]);
                }

                \Yii::$app->twitterProxy->hit($proxy, $proxyHit);
            } catch (ParserWrongResultException $e) {
                \Yii::error($e->data);

                $attempt++;
                continue;
            } catch (\Throwable $e) {
                \Yii::error([
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'uri' => $uri,
                    'code' => json_encode($options),
                ]);
                $attempt++;

                continue;
            }
            break;
        } while($attempt <= self::RETRY_COUNT);

        if(!($response instanceof ResponseInterface)) {
            throw new ParserException('Wrong response result');
        }
        else if($response->getStatusCode() !== 200) {
            throw new ParserException('Twitter wrong response: ' . $response->getBody(), null, $response->getStatusCode());
        }

        return $response;
    }

    /**
     * @return SauronResultPayload|void
     */
    protected function perform()
    {
        /** @var ScanData $data */
        $data = $this->request->data;
        $cookies = null;
        try {
            // first request to get cookies
            $response = $this->request('GET', 'explore', [
                'on_stats' => function (TransferStats $stats) use (&$cookies) {
                    if($stats->getResponse() instanceof ResponseInterface) {
                        $cookies = GuzzleHelper::extractCookies($stats->getResponse(), new Uri(self::BASE_URI));
                    }
                },
            ]);
            // extracting cookies for further usage
            if($response instanceof ResponseInterface) {
                $cookies = GuzzleHelper::extractCookies($response, new Uri(self::BASE_URI));
            }
        }
        catch(TooManyRedirectsException $e) {
            // doing nothing
        }
        catch(\Throwable $e) {
            // doing nothing
        }

        $options = [
            'cookies' => $cookies,
        ];

        $words = $data->words;
        foreach ($words as $key=>$word) {
            $word = str_replace('_', '', $word);
            if(false !== stripos($word, ' ')) {
                $words[$key] = '"'.$word.'"';
            }
        }
        $words = implode(' OR ', $words);
        $wordsPart = $words;
        if(!empty($data->minus_words)) {
            $minusWords = '';
            foreach ($data->minus_words as $minusWord) {
                $minusWord = str_replace('_', '', $minusWord);
                if(false !== stripos($minusWord, ' ')) {
                    $minusWord = '"'.$minusWord.'"';
                }
                $minusWords .= '-' . $minusWord . ' ';
            }
            $minusWords = trim($minusWords);
            $wordsPart .= ' ' . $minusWords;
        }

        $uri = self::MOBILE_URI . 'search?q='.urlencode($wordsPart);
        $response = $this->request('GET', $uri, $options);
        if(!preg_match('/gt=(\d+)/', $response->getBody(), $matches)) {
            throw new \yii\base\ErrorException('Error getting x-guest-token');
        }
        $guest_token = $matches[1];

        // define new referer
        $options['headers']['referer'] = self::BASE_URI . 'search?q=' . urlencode($wordsPart) . '&src=typed_query&f=live';
        $options['headers']['authorization'] = 'Bearer '.self::GUEST_BEARER;
        $options['headers']['x-guest-token'] = $guest_token;

        $uri = self::API_URI . 'search/adaptive.json?include_profile_interstitial_type=1&include_blocking=1&include_blocked_by=1&include_followed_by=1&include_want_retweets=1&include_mute_edge=1&include_can_dm=1&include_can_media_tag=1&skip_status=1&cards_platform=Web-12&include_cards=1&include_composer_source=true&include_ext_alt_text=true&include_reply_count=1&tweet_mode=extended&include_entities=true&include_user_entities=true&include_ext_media_color=true&include_ext_media_availability=true&send_error_codes=true&simple_quoted_tweets=true&q='.urlencode($wordsPart).'&tweet_search_mode=live&count=20&query_source=typed_query&pc=1&spelling_corrections=1&ext=mediaStats%2CcameraMoment';
        $response = $this->request('GET', $uri, $options);

        $oldCursor = null;
        [$entries, $cursor] = $this->parseData($response->getBody(), $data->words);

        $i = 0;
        while (!empty($cursor) && $oldCursor !== $cursor) {
            try {
                $uri = self::API_URI . 'search/adaptive.json?include_profile_interstitial_type=1&include_blocking=1&include_blocked_by=1&include_followed_by=1&include_want_retweets=1&include_mute_edge=1&include_can_dm=1&include_can_media_tag=1&skip_status=1&cards_platform=Web-12&include_cards=1&include_composer_source=true&include_ext_alt_text=true&include_reply_count=1&tweet_mode=extended&include_entities=true&include_user_entities=true&include_ext_media_color=true&include_ext_media_availability=true&send_error_codes=true&simple_quoted_tweets=true&q='.urlencode($wordsPart).'&tweet_search_mode=live&count=20&query_source=typed_query&pc=1&spelling_corrections=1&ext=mediaStats%2CcameraMoment&cursor=' . $cursor;
                $response = $this->request('GET', $uri, $options);
                $oldCursor = $cursor;

                [$newData, $cursor] = $this->parseData($response->getBody(), $data->words);
                $newEntries = [];
                $break = false;
                foreach ($newData as $entry) {
                    /** @var Entry $entry */
                    if ($data->min_time && $data->min_time >= $entry->created) {
                        $break = true;
                        break;
                    }
                    $newEntries[] = $entry;
                }
                $entries = array_merge($entries, $newEntries);

                if($break) {
                    break;
                }
                if (!empty($data->limit) && count($entries) >= $data->limit) {
                    break;
                }
                if (++$i >= self::MAX_ITERATIONS) {
                    break;
                }
            }
            catch(\Throwable $e) {
                \Yii::error([
                    'msg' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                break;
            }
        }
        // убираем все, что старше заданного времени
        foreach ($entries as $key=>$entry) {
            /** @var Entry $entry */
            if ($data->min_time && $data->min_time >= $entry->created) {
                unset($entries[$key]);
            }
        }

        $responseData = new ScanResponse();
        $responseData->setAttributes ([
            'theme_id' => $data->theme_id,
            'entries' => $entries,
        ]);

        if (!$responseData->validate()) {
            throw new ParserWrongResultException('Error validating result data: ' . json_encode($responseData->errors));
        }

        $this->response->data = $responseData;
    }

    /**
     * @param $json
     * @return array
     */
    protected function parseData($json, $words) {
        $json = json_decode($json, true);
        $tweets = ArrayHelper::getValue($json, 'globalObjects.tweets', []);
        $instructions = ArrayHelper::getValue($json, 'timeline.instructions', []);
        $cursor = null;
        foreach ($instructions as $instruction) {
            if(!empty($instruction['addEntries'])) {
                $timeline = ArrayHelper::getValue($instruction, 'addEntries.entries', []);
            }
            if('sq-cursor-bottom' === ArrayHelper::getValue($instruction, 'replaceEntry.entryIdToReplace')) {
                $cursor = ArrayHelper::getValue($instruction, 'replaceEntry.entry.content.operation.cursor.value');
            }
        }
        $users = ArrayHelper::getValue($json, 'globalObjects.users', []);

        $entries = [];
        foreach ($timeline as $item) {
            $tweetId = ArrayHelper::getValue($item, 'content.item.content.tweet.id');
            if(!is_numeric($tweetId)) {
                if('sq-cursor-bottom' === ArrayHelper::getValue($item, 'entryId')) {
                    $cursor = ArrayHelper::getValue($item, 'content.operation.cursor.value');
                }

                continue;
            }

            $tweet = $tweets[$tweetId];
            $text = ArrayHelper::getValue($tweet, 'full_text');
            $userId = ArrayHelper::getValue($tweet, 'user_id_str');
            $picture = ArrayHelper::getValue($tweet, 'entities.media.0.media_url_https');
            $user = $users[$userId];
            $username = ArrayHelper::getValue($user, 'name');
            $userlogin = ArrayHelper::getValue($user, 'screen_name');
            $userpic = ArrayHelper::getValue($user, 'profile_image_url_https');
            $link = self::BASE_URI . "{$userlogin}/status/{$tweetId}";
            $created = strtotime(ArrayHelper::getValue($tweet, 'created_at'));

            $isReply = false;
            if(ArrayHelper::getValue($tweet, 'in_reply_to_status_id', false)) {
                $isReply = true;
            }

            // если слово встречается в логине или имени пользователя, то пропускаем, т.к. нам важно, чтобы упоминание было именно в тексте поста
            $continue = false;
            $replyMention = 0;
            foreach ($words as $word) {
                if ((false !== stripos($userlogin, $word) || false !== stripos($username, $word)) && false === stripos($text, $word)) {
                    $continue = true;
                    break;
                }
                if ($isReply && false !== stripos($text, $word)) {
                    $replyMention++;
                }
            }
            if($continue) {
                continue;
            }
            if($isReply && $replyMention === 0) {
                continue;
            }

            $entry = new Entry();
            $entry->setAttributes([
                'external_id' => $tweetId,
                'link' => $link,
                'media' => $picture,
                'user_id' => $userId,
                'username' => $username,
                'userlogin' => $userlogin,
                'userpic' => $userpic,
                'text' => $text,
                'source' => 'twitter',
                'meta' => [
                    'replies' => ArrayHelper::getValue($tweet, 'reply_count'),
                    'retweets' => ArrayHelper::getValue($tweet, 'retweet_count'),
                    'likes' => ArrayHelper::getValue($tweet, 'favorite_count'),
                ],
                'created' => $created,
            ]);
            if(!$entry->validate()) {
                \Yii::error([
                    'msg' => 'Error validating Entry',
                    'data' => json_encode($entry->attributes),
                    'errors' => json_encode($entry->errors)
                ]);
                continue;
            }

            $entries[] = $entry;
        }

        return [$entries, $cursor];
    }
}