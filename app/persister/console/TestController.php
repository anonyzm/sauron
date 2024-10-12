<?php

namespace persister\console;

use common\console\BaseController;
use common\helpers\TimestampHelper;
use common\models\Account;
use common\models\Mention;
use common\models\presentation\MentionsDay;
use common\models\presentation\PresentationMention;
use common\models\Theme;
use GuzzleHttp\Client;
use ladno\proxyconveyor\components\mysqlconveyor\MysqlProxyConveyor;
use ladno\proxyconveyor\interfaces\ProxyInterface;
use ladno\proxyconveyor\components\mysqlconveyor\models\Proxy;
use ladno\instaparserlib\models\ApiResponse;
use common\models\transport\data\PresentationData;
use common\models\transport\data\ScanData;
use common\models\transport\data\TimezoneData;
use common\models\transport\Entry;
use common\models\transport\responses\ScanResponse;
use common\models\transport\payloads\SauronActionPayload;
use common\models\transport\payloads\SauronResultPayload;
use ladno\proxyconveyor\models\ProxyHit;
use ladno\woody\components\rabbit\Producer;
use ladno\woody\models\MQMessage\Error;
use ladno\woody\models\MQMessage\Message;
use PHPHtmlParser\Dom;
use yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Test actions
 *
 * @package console\controllers
 */
class TestController extends BaseController
{
    protected $rabbit = 'rabbit';
    protected $exchange = 'persister-exchange';

    public function actionScan($words, $min_time = null)
    {
        if (false !== stripos($words, ',')) {
            $words = explode(',', $words);
        }
        if (!is_array($words)) {
            $words = [$words];
        }
        if(!$min_time) {
            $min_time = time() - 3600;
        }
        $result = $this->rpc('scan', new ScanData([
            'words' => $words,
            'min_time' => $min_time,
        ]));

        /** @var ScanResponse $resultData */
        $resultData = $result->data;
        foreach ($resultData->entries as $word=>$entries) {
            echo "WORD: {$word}".PHP_EOL;
            foreach ($entries as $entry) {
                /** @var Entry $entry */
                echo date('d.m.Y H:i', $entry->created) . ' - ' . mb_substr($entry->text, 0, 100) . PHP_EOL;
            }
        }

        //echo $result . PHP_EOL;
    }

    /**
     * Базовый метод для rpc запросов
     * @param $action
     * @param array $data
     * @return SauronResultPayload|mixed
     */
    protected function rpc($action, $data = [])
    {
        $result = null;
        $msg = null;
        try {
            $msg = new SauronActionPayload([
                'action' => $action,
                'data' => $data
            ]);

            if (!$msg->validate()) {
                throw new yii\base\Exception('Error validating action payload' . json_encode($msg->errors));
            }

            /** @var Producer $rabbit */
            $rabbit = \Yii::$app->{$this->rabbit};
            $response = $rabbit->exchange($this->exchange)->rpc(
                [
                    'body' => $msg,
                ]);

            if (!($response instanceof Message)) {
                throw new yii\base\Exception('Invalid response, please try again.');
            }

            $result = $response->body;
        } catch (\Exception $e) {
            $result = new SauronResultPayload([
                'client_name' => 'unknown',
                'status' => 'fail',
                'message' => $msg,
                'error' => new Error([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]),
            ]);
        }

        return $result;
    }

    public function actionConvert() {
        $domParser = new Dom();
        $domParser->load(file_get_contents('/app/tttyyy.html'));
        $elems = $domParser->find('.tweet');
        $entries = [];
        foreach ($elems as $tweet) {
            echo "123";
            $id = $tweet->getAttribute('data-tweet-id');
            $entry = new Entry();

            $entry->setAttributes([
                'external_id' => $id,
                'link' => $tweet->getAttribute('data-permalink-path'),
                'user_id' => $tweet->getAttribute('data-user-id'),
                'username' => $tweet->getAttribute('data-name'),
                'userpic' => $tweet->find('.stream-item-header a.account-group img')->src,
                'text' => $tweet->find('.js-tweet-text-container p')->innerHtml,
                'meta' => [
                    'replies' => $tweet->find("#profile-tweet-action-reply-count-aria-{$id}")->innerHtml,
                    'retweets' => $tweet->find("#profile-tweet-action-retweet-count-aria-{$id}")->innerHtml,
                    'likes' => $tweet->find("#profile-tweet-action-favorite-count-aria-{$id}")->innerHtml,
                ],
                'created' => $tweet->find('.stream-item-header .time span._timestamp')->getAttribute('data-time'),
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

        // сортируем, свежие сверху
        usort($entries, function (Entry $a, Entry $b) {
            if ($a->created === $b->created) return 0;
            return ($a->created > $b->created) ? -1 : 1;
        });

        return $entries;
    }

    public function actionErr() {
        $mention = Mention::find()->orderBy(['created' => SORT_DESC])->one();
        var_dump($mention->attributes);
    }

    public function actionPresent() {
        // сообщаем персистеру о том, что можно перекладывать
        $message = \Yii::$app->rabbit->createMessage([
            'body' => new SauronActionPayload([
                'action' => 'presentation',
                'data' => new PresentationData([
                    'theme_id' => '5d5bf2a2de26c14e12f13dec',
                ]),
            ]),
        ]);
        Yii::$app->rabbit->exchange(Yii::$app->params['presentationExchange'])
            ->routingKey(Yii::$app->params['presentationRoutingKey'])
            ->push($message);
    }

    public function actionThemeInfo($theme_id) {
        $theme = Theme::findOne(['_id' => $theme_id]);
        $lastMention = Mention::find()->where(['theme_id' => $theme->id])->orderBy(['created' => SORT_DESC])->one();
        $lastPresentationMention = PresentationMention::find()->where(['theme_id' => $theme->id])->orderBy(['created' => SORT_DESC])->one();

        Console::output("Last mention: ". date('d.m.Y H:i', $lastMention->created));
        Console::output("Last presentation mention: ". date('d.m.Y H:i', $lastPresentationMention->created));
    }

    public function actionPresentate($theme_id) {
        try {
            $theme = Theme::findOne(['_id' => $theme_id]);
            $allowedLimit = $theme->allowedLimit;
            $from = time();
            if($from % 86400 < 600) {
                $from -= 600;
            }
            $from = TimestampHelper::dayStart($from, $theme->account->timezone);
            $to = $from + 86400;

            $lastPresentationTimes = [];
            foreach ($theme->sources as $source) {
                $lastPresentationTime = 0;
                $lastPresentationMention = PresentationMention::find()->where(['theme_id' => $theme_id, 'source' => $source])->orderBy(['created' => SORT_DESC])->one();
                if($lastPresentationMention) {
                    $lastPresentationTime = $lastPresentationMention->created;
                }
                $lastPresentationTimes[$source] = $lastPresentationTime;
            }
            $mentionQuery = Mention::find()->where([
                'and',
                ['>=', 'created', $from],
                ['<', 'created', $to],
                ['=', 'theme_id', $theme_id],
            ]);

            // если лимит еще не истек
            if($allowedLimit > 0) {
                $limitCursor = 0;
                $limitReached = false;
                $mentionsDays = [];
                foreach ($mentionQuery->batch() as $mentions) {
                    $mentionsToInsert = [];
                    foreach ($mentions as $mention) {
                        /** @var Mention $mention */
                        // записываем в группирующую таблицу
                        $dayTimestamp = TimestampHelper::dayStart($mention->created, $theme->account->timezone);
                        $mentionsDays[$dayTimestamp] = isset($mentionsDays[$dayTimestamp]) ? ($mentionsDays[$dayTimestamp] + 1) : 1;

                        if(!isset($lastPresentationTimes[$mention->source])) {
                            continue;
                        }
                        else if($mention->created <= $lastPresentationTimes[$mention->source]) {
                            continue;
                        }

                        $presentationMention = PresentationMention::fromMention($mention);
                        if ($presentationMention) {
                            $attributes = $presentationMention->attributes;
                            unset($attributes['_id']);

                            $mentionsToInsert[] = $attributes;
                            $limitCursor++;
                        }

                        if ($limitCursor >= $allowedLimit) {
                            $limitReached = true;
                            break;
                        }
                    }

                    // записываем данные самих упоминаний
                    if (count($mentionsToInsert)) {
                        $insertIds = Yii::$app->mongodb->createCommand()->batchInsert(PresentationMention::collectionName(), $mentionsToInsert);
                        if (count($mentionsToInsert) !== count($insertIds)) {
                            \Yii::error([
                                'msg' => 'Number of rows doesn\'t match number of rows inserted!',
                                'toInsert' => json_encode($mentionsToInsert),
                                'insertIds' => json_encode($insertIds)
                            ]);
                        }
                    }

                    if ($limitReached) {
                        break;
                    }
                }

                var_dump($mentionsDays);
                $days = array_keys($mentionsDays);
                foreach ($days as $dayTimestamp) {
                    $mentionsDay = MentionsDay::findOne(['day_timestamp' => $dayTimestamp, 'theme_id' => $theme->id]);
                    if(!$mentionsDay) {
                        $mentionsDay = new MentionsDay();
                        $mentionsDay->theme_id = $theme->id;
                        $mentionsDay->day_timestamp = $dayTimestamp;
                    }
                    $mentionsDay->count = $mentionsDays[$dayTimestamp];
                    if (!$mentionsDay->save()) {
                        \Yii::error([
                            'msg' => 'Error updating MentionsDay count',
                            'errors' => json_encode($mentionsDay->errors),
                        ]);
                    }
                    var_dump($mentionsDay->attributes);
                }
                var_dump($theme->attributes);

                // вычитаем лимиты из темы и удаляем persisted
                $theme->limit -= $limitCursor;
                $theme->persisted = null;
                if (!$theme->save()) {
                    \Yii::error([
                        'msg' => 'Error updating Theme limits',
                        'errors' => json_encode($theme->errors),
                    ]);
                }
                // вычитаем лимиты из аккаунта
                $account = Account::findOne(['_id' => $theme->account_id]);
                $account->limit -= $limitCursor;
                if (!$account->save()) {
                    \Yii::error([
                        'msg' => 'Error updating Account limits',
                        'errors' => json_encode($account->errors),
                    ]);
                }
            }
        }
        catch (\Throwable $e) {
            \Yii::error($e);
        }
    }

    public function actionCreateTest($service_id, $skip = false)
    {
        if($skip === '1' || $skip === 'true') {
            $skip = true;
        }
        if ((Account::find()->count() > 0) && !$skip) {
            Console::output('DB is not empty, skipping test creation...');
            return;
        }

        $acc = new Account();
        $acc->setAttributes([
            'service_id' => $service_id,
            'external_id' => md5(time()),
            'alias' => 'Test_'.time(),
            'timezone' => 'Europe/Moscow',
            'limit' => 50000000,
            'maxLimit' => 50000000,
            'collected' => 0,
        ]);
        if(!$acc->save()) {
            var_dump($acc->errors);
            return;
        }
        Console::output("Account #{$acc->id} created");
        $file = file_get_contents('/app/persister/test/words.txt');
        preg_match_all('/\d+\s(\w+)/', $file, $matches, PREG_SET_ORDER);
        $words = [];
        foreach ($matches as $match) {
            $words[] = trim($match[1]);
        }

        $theme = null;
        $key = 0;
        foreach ($words as $word) {
            if(strlen($word) <= 3) {
                continue;
            }
            if($key % 5 === 0) {
                if($theme instanceof Theme) {
                    if(!$theme->save()) {
                        var_dump($theme->errors);
                    }
                    Console::output("Theme `{$theme->name}` created");
                }
                $theme = new Theme();
                $theme->name = $word.'_'.time();
                $theme->account_id = $acc->id;
                $theme->limit = 50000;
                $theme->maxLimit = 50000;
                $theme->sources = ['twitter', 'instagram'];
                $theme->minusWords = [];
                $theme->words = [];
            }

            $theme->words = yii\helpers\ArrayHelper::merge([$word], $theme->words);
            $key++;
        }

        if($theme instanceof Theme && count($theme->words) > 0) {
            if(!$theme->save()) {
                var_dump($theme->errors);
            }
        }

        Console::output('Test created');
    }

    public function actionStat($page = 1, $maxPage = 22) {
        $client = new Client([
            'base_uri' => 'http://148.251.11.194:84/',
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
        $sources = [];
        $times = [];
        for($i = $page; $i < $maxPage; $i++) {
            $data = $client->request('GET', 'api/scan-stat?page='.$i, [
                'headers' => [
                    'Authorization' => 'Bearer xrimjG38iW3g0l4jDsC5j55BVhYvRvhl',
                ],
            ]);
            $data = json_decode($data->getBody(), true);
            foreach ($data as $item) {
                $sources[$item['source']] = isset($sources[$item['source']]) ? $sources[$item['source']] + $item['count'] : $item['count'];
                $times[$item['source']] = isset($times[$item['source']]) ? $times[$item['source']] + $item['time'] : $item['time'];
            }
        }

        foreach ($sources as $key=>$count) {
            Console::output("$key: " . ($times[$key] / $count));
        }
    }

    public function actionRecalc($theme_id) {
        $theme = Theme::findOne($theme_id);
        $message = Yii::$app->rabbit->createMessage([
            'body' => new SauronActionPayload([
                'action' => 'timezone',
                'data' => new TimezoneData([
                    'theme_id' => $theme->id,
                    'timezone' => $theme->account->timezone,
                ]),
            ]),
        ]);
        Yii::$app->rabbit->exchange(Yii::$app->params['presentationExchange'])
            ->routingKey(Yii::$app->params['presentationRoutingKey'])
            ->push($message);
    }

    public function actionTtest() {
        new ApiResponse([
                'scenario' => ApiResponse::TAG_SCENARIO,
                'data' => null,
                'paging' => [
                    'end_cursor' => null,
                    'has_next_page' => false
                ],
                'find_text' => 'fuck2'
            ]
        );
    }

    public function actionPp() {
        $tags = ['test','nike','girl','friend','dude','trump','putin','china','hongkong','kingkong','theatre','socks','women','men','trap','key','bizarro','php','tutor','elephant','bear','lion','pride','gay','clown','local'];
        $tagCount = count($tags);
        for ($i = 0; $i < 1000; $i++) {
            $tt = microtime(true);
            echo '['.($i + 1).'] Collecting .... ';
            $tag = $tags[$i % $tagCount];
            $result = \Yii::$app->instaparser->getTagMedia($tag);
            echo count($result->data).' collected ';
            echo '['.(microtime(true) - $tt).']'.PHP_EOL;
        }
        echo 'FINISHED!'.PHP_EOL;
    }

    public function actionPj() {
        echo json_encode([
                '77.83.86.240:8085','77.83.85.51:8085','83.171.255.74:8085','193.202.86.120:8085','45.148.125.164:8085','45.145.131.214:8085','193.187.93.153:8085','109.94.172.119:8085','45.138.101.202:8085','212.119.41.234:8085','83.171.254.199:8085','193.187.92.96:8085','212.119.42.81:8085','91.188.247.185:8085','193.202.15.217:8085','193.56.72.99:8085','89.191.228.101:8085','85.208.209.137:8085','77.83.86.27:8085','193.202.8.53:8085','91.188.247.251:8085','213.166.78.194:8085','89.191.229.119:8085','193.202.13.47:8085','88.218.65.237:8085','91.243.92.117:8085','91.188.247.158:8085','185.88.101.136:8085','45.145.131.221:8085','193.187.95.83:8085','85.209.149.219:8085','2.57.78.15:8085','212.119.41.206:8085','31.40.254.162:8085','193.202.86.20:8085','31.40.208.250:8085','193.187.93.123:8085','31.40.252.31:8085','85.208.210.14:8085','88.218.67.96:8085','31.40.255.28:8085','213.166.79.44:8085','45.148.125.219:8085','89.191.229.25:8085','37.44.254.93:8085','85.209.149.38:8085','45.67.212.167:8085','77.83.87.234:8085','45.66.209.20:8085','193.56.64.25:8085','45.67.212.47:8085','193.56.72.156:8085','85.208.210.237:8085','88.218.65.38:8085','85.208.87.195:8085','45.145.131.147:8085','31.40.255.246:8085','212.119.40.161:8085','193.202.87.33:8085','178.159.107.237:8085','85.208.86.232:8085','45.80.106.217:8085','146.185.204.80:8085','45.67.214.109:8085','193.202.86.227:8085'
        ]).PHP_EOL;
    }

    public function actionInc($inc = 1) {
        $collection = Yii::$app->mongodb->getCollection(Account::collectionName());
        $collection->update(['_id' => '5e020ec337cc0b00ec65cb92'], ['$inc' => ['collected' => (int) $inc, 'maxLimit' => (int) (-1 * $inc)]]);
    }

    public function actionMentionAttr() {
        $collection = Yii::$app->mongodb->getCollection(Mention::collectionName());
        $collection->aggregate([
            [
                '$addFields' => [
                    'media' => ['$ifNull' => ['$picture', '$media']]
                ],
            ],
            [
                '$out' => Mention::collectionName()
            ]
        ]);
    }

    public function actionComp() {
        $tt = microtime(true);
        $components = Yii::$app->getComponents();
        foreach ($components as $componentName=>$component) {
            $class = ArrayHelper::getValue($component, 'class');
            if (!$class) {
                continue;
            }
            if (false !== stripos($class, 'ladno\instaparserlib\IgClient')) {
                if (Yii::$app->$componentName->proxy instanceof ProxyInterface) {
                    Console::output(Yii::$app->$componentName->proxy->dbComponent);
                }
                break;
            }
        }
        Console::output(microtime(true) - $tt);
    }

    public function actionAr() {
        $result = Yii::$app->instaparser->getTagMedia('nike', 10);
        var_dump($result); die();

        $proxies = Proxy::db('db1')::find()->all();
        foreach ($proxies as $proxy) {
            Console::output(json_encode($proxy->attributes));
        }
    }

    public function actionMysql() {
        /** @var MysqlProxyConveyor $conveyor */
        $conveyor = Yii::$app->mysqlProxy;
        $proxy = $conveyor->get();
        Console::output((string) $proxy);
        $proxyHit = new ProxyHit();
        $proxyHit->setAttributes([
            'failed' => 1,
            'microtime' => rand(5,15) * 100,
            'status' => 400,
            'url' => 'https://yandex.ru/search',
            'data' => [],
        ]);
        $conveyor->hit($proxy, $proxyHit);
    }
}