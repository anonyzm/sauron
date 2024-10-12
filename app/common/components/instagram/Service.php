<?php

namespace common\components\instagram;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\EachPromise;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Service
 * @package common\components\instagram
 * @deprecated все общение с инстой через instaparser-lib
 */
class Service
{
    /** @var Client */
    private $_client = null;
    /*
        странный лимит, на сайте он мотает с динамическим значением, оставляя хвост из очень старых постов.
        Указывая 50, приходит около 200
    */
    protected const LIMIT = 50;

    public function getClient(): Client
    {
        if (is_null($this->_client)) {
            $this->_client = new Client([
                'base_uri' => ArrayHelper::getValue(Yii::$app->params, 'instaparser.baseUrl'),
                'connect_timeout' => 30.0, // Give up trying to connect after 30s.
                'decode_content' => true, // Decode gzip/deflate/etc HTTP responses.
                // парсер делает максимум 10 попыток с лимитом в 100 сек на запрос
                'timeout' => 180.0, // Maximum per-request time (seconds).
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
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function request($method, $uri, $options = [])
    {
        return $this->getClient()->request($method, $uri, $options);
    }

    /**
     * @param array $requests
     * @param callable $callback
     */
    public function batch($requests, $callback, $concurrency = 10) {
        $client = $this->getClient();
        $promises = (function () use ($client, $requests) {
            foreach ($requests as $request) {
                yield $client->requestAsync($request[0], $request[1]);
            }
        })();

        $eachPromise = new EachPromise($promises, [
            'concurrency' => $concurrency,
            'fulfilled' => $callback
        ]);
        $eachPromise->promise()->wait();
    }

    public function loadMedia(string $tag, ?string $url = ''): ?ResponseInterface
    {
        try {
            if ($url) {
                Yii::debug($url);
                $response = $this->request('GET', $url);
            } else {
                Yii::debug($tag);
                $response = $this->request('GET',
                    'tag-media',
                    [
                        'query' => [
                            'tag' => $tag,
                            'first' => self::LIMIT,
                            'prefer' => ArrayHelper::getValue(Yii::$app->params, 'instaparser.prefer'),
                            'service' => ArrayHelper::getValue(Yii::$app->params, 'instaparser.service')
                        ]
                    ]);
            }
            $status = $response->getStatusCode();
            if ($status !== 200) {
                Yii::error([
                    'msg' => 'Wrong response status code',
                    'tag' => $tag,
                    'code' => $status,
                    'body' => $response->getBody(),
                ]);
                return null;
            }
            Yii::debug('from service ' . json_encode($response->getBody()), true);

            return $response;
        } catch (GuzzleException $e) {
            Yii::error(print_r($e, true));
            return null;
        }
    }
}