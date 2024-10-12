<?php

$params = \yii\helpers\ArrayHelper::merge(
    require(dirname(dirname(__DIR__)) . '/common/config/params.php'),
    require(__DIR__ . '/params.php')
);

$config = [
    'id' => 'operator-console',
    'controllerNamespace' => 'operator\console',
    'bootstrap' => ['log', 'operator-consumer'],
    'components' => [
        'log' => require(__DIR__ . '/log.php'),
        'twitterProxy' => [
            'class' => \ladno\proxyconveyor\components\apiconveyor\ApiProxyConveyor::class,
            'apiUrl' => getenv('TWITTER_PROXY_API_URL'),
            'authToken' => getenv('PROXY_AUTH_TOKEN'),
        ],
        'operator-consumer' => [
            'class' => 'ladno\woody\components\rabbit\Consumer',
            'ttr' => 480,
            'consumeExchange' => $params['operatorExchange'],
            'consumeQueue' => $params['operatorQueue'],
            'consumeRoutingKey' => $params['operatorRoutingKey'],
            'handler' => ['operator\components\OperatorCallback', 'perform'],
        ],
    ],
    'params' => $params,
];

return $config;