<?php

$config = [
    'id' => 'api',
    'bootstrap' => ['log', 'v1'],
    'controllerNamespace' => 'api\controllers',
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'log' => require(__DIR__ . '/log.php'),
        'user' => [
            'identityClass' => 'api\models\ServiceIdentity',
            'enableAutoLogin' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'modules' => [
        'v1' => 'api\modules\api\v1\Module',
    ],
    'controllerMap' => [
        'instagram-proxy' => [
            'class' => \ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\controllers\ProxyController::class,
            'authToken' => getenv('PROXY_AUTH_TOKEN'),
            'proxyPool' => ['instagram'],
        ],
        'twitter-proxy' => [
            'class' => \ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\controllers\ProxyController::class,
            'authToken' => getenv('PROXY_AUTH_TOKEN'),
            'proxyPool' => ['twitter'],
        ],
        'reddit-auth' => [
            'class' => \api\controllers\RedditAuthController::class,
            'authToken' => getenv('REDDIT_AUTH_TOKEN'),
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
    $config['modules']['debug']['panels'][] = 'yii\mongodb\debug\MongoDbPanel';
    $config['modules']['debug']['panels'][] = 'yii\httpclient\debug\HttpClientPanel';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;