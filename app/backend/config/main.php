<?php
$params = \yii\helpers\ArrayHelper::merge(
    require(dirname(dirname(__DIR__)) . '/common/config/params.php'),
    require(__DIR__ . '/params.php')
);

$config = [
    'id' => 'backend',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'backend\controllers',
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
        ],
        'log' => require(__DIR__ . '/log.php'),
        'user' => [
            'identityClass' => 'backend\models\User',
            'enableAutoLogin' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
    'params' => $params,
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