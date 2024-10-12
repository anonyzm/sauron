<?php

namespace common\components\reddit;

use common\models\auth\RedditUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class Service extends BaseObject
{
    const AUTH_URL = 'https://www.reddit.com/api/v1/access_token';
    const API_URL = 'https://oauth.reddit.com/';

    /** @var Client */
    private $_client = null;

    /** @var RedditAuthConveyor */
    protected $redditAuthConveyor = null;
    /** @var RedditUser */
    protected $user = null;
    /** @var string  */
    protected $accessToken = null;

    const MAX_ATTEMPTS = 3;

    public function init()
    {
        parent::init();

        $this->redditAuthConveyor = new RedditAuthConveyor([
            'apiUrl' => ArrayHelper::getValue(Yii::$app->params, 'reddit.apiUrl'),
            'authToken' => ArrayHelper::getValue(Yii::$app->params, 'reddit.authToken'),
        ]);
    }

    /**
     * @param string $tag
     * @param string|null $after
     * @param int $attempt
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function loadPosts(string $tag, ?string $after = '', int $attempt = 1): ?array
    {
        if(!$this->user) {
            $this->user = Yii::$app->reddit->get();
        }
        Yii::debug('loadPosts attempt: ' . $attempt);
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Yii::error('access token is null');
            return null;
        }
        if ($attempt > self::MAX_ATTEMPTS) {
            $this->user->status = RedditUser::STATUS_MAX_ERRORS;
            Yii::$app->reddit->hit($this->user);
            Yii::error('loadPosts: max attempts reached');
            return null;
        }
        $response = $this->request('GET', self::API_URL . 'r/all/search', [
            'query' => [
                'q' => $tag,
                'sort' => 'new',
                'after' => $after,
                'limit' => 200,
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'User-Agent' => ArrayHelper::getValue(Yii::$app->params, 'reddit.useragent'),
            ],
        ]);

        if ($response->getStatusCode() != 200) {
            $data = json_decode($response->getBody(), true);
            Yii::warning('status code ' . $response->getStatusCode() . ' ' . $data);
            $this->user = null;
            return $this->loadPosts($tag, $after, ++$attempt);
        }

        $remainingHeaders = $response->getHeader('x-ratelimit-remaining');
        $usedHeaders = $response->getHeader('x-ratelimit-used');
        $resetHeaders = $response->getHeader('x-ratelimit-reset');
        $remaining = reset($remainingHeaders);
        $used = reset($usedHeaders);
        $reset = reset($resetHeaders);
        if ($remaining !== false && $used !== false && $reset !== false) {
            $this->user->setRateLimit($remaining, $used, $reset);
            Yii::$app->reddit->hit($this->user);
        }
        Yii::debug('x-ratelimit-remaining ' . $remaining);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param int $attempt
     * @return string|null
     * @throws GuzzleException
     * @throws InvalidConfigException
     */
    public function getAccessToken(int $attempt = 1): ?string
    {
        Yii::debug('getAccessToken attempt: ' . $attempt);

        if ($this->accessToken) {
            Yii::debug('access token from ram ' . $this->accessToken);
            return $this->accessToken;
        }

        if ($attempt > self::MAX_ATTEMPTS) {
            Yii::error([
                'msg' => 'getAccessToken: max attempts reached',
                'attempt' => $attempt,
                'max_attempts' => self::MAX_ATTEMPTS,
            ]);
            return null;
        }

        if(!$this->user) {
            $this->user = Yii::$app->reddit->get();
        }
        if (!$this->user) {
            Yii::error('user not found');
            return null;
        }
        $username = $this->user->username;
        $password = $this->user->password;
        Yii::debug('current user ' . print_r($this->user->toArray(), true));
        $params = [
            'auth' => [
                ArrayHelper::getValue(Yii::$app->params, 'reddit.id'),
                ArrayHelper::getValue(Yii::$app->params, 'reddit.secret')
            ],
            'form_params' => [
                'grant_type' => 'password',
                'username' => $username,
                'password' => $password
            ],
            'headers' => [
                'User-Agent' => ArrayHelper::getValue(Yii::$app->params, 'reddit.useragent'),
            ],
        ];
        Yii::debug('request params ' . print_r($params, true));
        $response = $this->request('POST', self::AUTH_URL, $params);
        $data = json_decode($response->getBody(), true);
        Yii::debug($data);
        if (array_key_exists('error', $data)) {
            $this->user->status = RedditUser::STATUS_BANNED;
            Yii::$app->reddit->hit($this->user);
            $this->user = null;
            return $this->getAccessToken(++$attempt);
        }
        if (!array_key_exists('access_token', $data)) {
            $this->user = null;
            return $this->getAccessToken(++$attempt);
        }
        $this->accessToken = $data['access_token'];
        Yii::debug('access token from request ' . $this->accessToken);

        return $this->accessToken;
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

    public function getClient(): Client
    {
        if (is_null($this->_client)) {
            $this->_client = new Client([
                'base_uri' => self::API_URL,
                'connect_timeout' => 30.0, // Give up trying to connect after 30s.
                'decode_content' => true, // Decode gzip/deflate/etc HTTP responses.
                'timeout' => 30.0, // Maximum per-request time (seconds).
                // Tells Guzzle to stop throwing exceptions on non-"2xx" HTTP codes,
                // thus ensuring that it only triggers exceptions on socket errors!
                // We'll instead MANUALLY be throwing on certain other HTTP codes.
                'http_errors' => false,
            ]);
        }

        return $this->_client;
    }
}