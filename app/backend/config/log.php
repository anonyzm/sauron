<?php

if (getenv('SENTRY_ENABLED') == 'true') {
    $targets = [
        'error' => [
            'class' => 'notamedia\sentry\SentryTarget',
            'dsn' => getenv('SENTRY_DSN'),
            'levels' => ['error', 'warning'],
            'context' => true,
            'clientOptions' => [
                'environment' => getenv('SENTRY_ENVIRONMENT'),
                'release' => YII_APP_VERSION ?? null,
            ],
            'except' => [
                'yii\console\UnknownCommandException',
            ]
        ],
    ];
} else {
    $targets = [
        'error' => [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
            'logFile' => '@backend/runtime/logs/error.log',
            'logVars' => ['_SERVER.USER', '_SERVER.SCRIPT_NAME', '_SERVER.argv'],
            'exportInterval' => 1,
        ],
        'profile' => [
            'class' => 'yii\log\FileTarget',
            'levels' => ['profile'],
            'logFile' => '@backend/runtime/logs/profile.log',
            'logVars' => ['_SERVER.USER', '_SERVER.SCRIPT_NAME', '_SERVER.argv'],
            'exportInterval' => 1,
        ],
        'app' => [
            'class' => 'yii\log\FileTarget',
            'categories' => ['application', 'app*'],
            'levels' => ['info'],
            'logFile' => '@backend/runtime/logs/app.log',
            'logVars' => [],
            'exportInterval' => 1,
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['profile'],
            'logFile' => '@backend/runtime/logs/http-request.log',
            'categories' => ['yii\httpclient\*'],
        ],
    ];
}


return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => $targets,
    'flushInterval' => 1,
];