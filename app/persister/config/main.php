<?php
$params = \yii\helpers\ArrayHelper::merge(
    require(dirname(dirname(__DIR__)) . '/common/config/params.php'),
    require(__DIR__ . '/params.php')
);

$config = [
    'id' => 'persister-console',
    'controllerNamespace' => 'persister\console',
    'bootstrap' => ['log', 'persister-consumer', 'presentation-consumer', 'settings'],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\mongodb\console\controllers\MigrateController',
            'migrationPath' => [
                '@common/migrations',
                '@vendor/ladno/instaparserlib/httpclient/proxy/components/apiconveyor/drivers/mongodb/migrations',
            ],
        ],
        'proxy-manager' => [
            'class' => \ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\console\ProxyController::class,
        ],
    ],
    'components' => [
        'log' => require(__DIR__ . '/log.php'),
        'persister-consumer' => [
            'class' => 'ladno\woody\components\rabbit\Consumer',
            'ttr' => 300,
            'consumeExchange' => $params['persisterExchange'],
            'consumeQueue' => $params['persisterQueue'],
            'consumeRoutingKey' => $params['persisterRoutingKey'],
            'handler' => ['persister\components\PersistCallback', 'perform'],
        ],
        'presentation-consumer' => [
            'class' => 'ladno\woody\components\rabbit\Consumer',
            'ttr' => 300,
            'consumeExchange' => $params['presentationExchange'],
            'consumeQueue' => $params['presentationQueue'],
            'consumeRoutingKey' => $params['presentationRoutingKey'],
            'handler' => ['persister\components\PresentationCallback', 'perform'],
        ],
    ],
    'params' => $params,
];

return $config;