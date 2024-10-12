<?php
$params = require(__DIR__ . '/params.php');

return [
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Europe/Moscow',
    'components' => [
        'presentationMongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => getenv('PRESENTATION_MONGODB_DSN') ?: 'mongodb://' . getenv('PRESENTATION_MONGODB_HOST') . ':' . getenv('PRESENTATION_MONGODB_PORT') . '/' . getenv('PRESENTATION_MONGODB_DATABASE'),
            'driverOptions' => [
               'ca_file' => getenv('MONGO_CA_FILE') ?: null
            ],
        ],
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => getenv('MONGODB_DSN') ?: 'mongodb://' . getenv('MONGODB_HOST') . ':' . getenv('MONGODB_PORT') . '/' . getenv('MONGODB_DATABASE'),
            'driverOptions' => [
               'ca_file' => getenv('MONGO_CA_FILE') ?: null
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => getenv('REDIS_HOST'),
            'port' => getenv('REDIS_PORT'),
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'database' => getenv('REDIS_DATABASE') ?: 0,
        ],
        'mutex' => [
            'class' => 'yii\redis\Mutex',
            'keyPrefix' => 'mutex',
            'expire' => 3600,
            'redis' => 'redis'
        ],
        'rabbit' => [
            'class' => 'ladno\woody\components\rabbit\Producer',
            'host' => getenv("RABBIT_HOST"),
            'port' => getenv("RABBIT_PORT"),
            'vhost' => getenv("RABBIT_VHOST"),
            'user' => getenv("RABBIT_USER"),
            'password' => getenv("RABBIT_PASSWORD"),
            'heartbeat' => getenv("RABBIT_HEARTBEAT"),
            'readTimeout' => getenv("RABBIT_READWRITETIMEOUT"),
            'writeTimeout' => getenv("RABBIT_READWRITETIMEOUT"),

            'api' => [
                'url' => getenv("RABBIT_API_URL"),
                'user' => getenv("RABBIT_API_USER"),
                'password' => getenv("RABBIT_API_PASSWORD"),
            ],
        ],
        'settings' => [
            'class' => '\ladno\yii2settings\Component',
            'connection' => 'mongodb',
            'table' => 'settings',
            'settingsFile' => '@common/config/settings.php',
        ],
        'proxyProvider' => [
            'class' => \common\components\FineProxyProvider::class,
            'login' => getenv('FINEPROXY_LOGIN'),
            'password' => getenv('FINEPROXY_PASSWORD'),
        ],
        'instaparser' => [
            'class' => \ladno\instaparserlib\IgClient::class,
            'httpClient' => [
                'class' => \ladno\instaparserlib\httpclient\IgHttpClient::class,
                'retry' => \ladno\instaparserlib\Retry::class,
                'proxy' => [
                    'class' => \ladno\proxyconveyor\components\apiconveyor\ApiProxyConveyor::class,
                    'apiUrl' => getenv('INSTAGRAM_PROXY_API_URL'),
                    'authToken' => getenv('PROXY_AUTH_TOKEN'),
                ],
            ],
        ],
        'reddit' => [
            'class' => \common\components\reddit\RedditAuthConveyor::class,
            'apiUrl' => getenv('REDDIT_API_URL'),
            'authToken' => getenv('REDDIT_AUTH_TOKEN'),
        ],
    ],
    'params' => $params,
];
