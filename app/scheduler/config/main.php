<?php
$params = \yii\helpers\ArrayHelper::merge(
    require(dirname(dirname(__DIR__)) . '/common/config/params.php'),
    require(__DIR__ . '/params.php')
);

$config = [
    'id' => 'scheduler-console',
    'controllerNamespace' => 'scheduler\console',
    'bootstrap' => ['log'],
    'components' => [
        'log' => require(__DIR__ . '/log.php'),
        'slack' => [
            'class' => 'understeam\slack\Client',
            'httpclient' => [
                'class' => 'yii\httpclient\Client',
            ],
            'url' => getenv('MATTERMOST_WEBHOOK'),
            'username' => 'Sauron',
        ],
    ],
    'params' => $params,
];

return $config;