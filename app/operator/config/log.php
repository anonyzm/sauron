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
            ],
            'extraCallback' => function ($message, $extra) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($extra): void {
                    if (is_array($extra) && !empty($extra)) {
                        foreach ($extra as $key => $value) {
                            // пропускаем, тут все переменные сервера и они не нужны
                            if ($key != 'context') {
                                $scope->setExtra((string)$key, $value);
                            }
                        }
                    }
                });
                return $extra;
            },
        ],
    ];
} else {
    $targets = [
        'error' => [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
            'logFile' => '@operator/runtime/logs/error.log',
            'logVars' => ['_SERVER.USER', '_SERVER.SCRIPT_NAME', '_SERVER.argv'],
            'exportInterval' => 1,
        ],
        'profile' => [
            'class' => 'yii\log\FileTarget',
            'levels' => ['profile'],
            'logFile' => '@operator/runtime/logs/profile.log',
            'logVars' => ['_SERVER.USER', '_SERVER.SCRIPT_NAME', '_SERVER.argv'],
            'exportInterval' => 1,
        ],
        'app' => [
            'class' => 'yii\log\FileTarget',
            'categories' => ['application', 'app*'],
            'levels' => ['info', 'trace'],
            'logFile' => '@operator/runtime/logs/app.log',
            'logVars' => [],
            'exportInterval' => 1,
        ],
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['profile'],
            'logFile' => '@operator/runtime/logs/http-request.log',
            'categories' => ['yii\httpclient\*'],
        ],
    ];

    $targets['woody'] = [
        'class' => 'yii\log\FileTarget',
        'levels' => ['trace'],
        'categories' => ['woody'],
        'logFile' => '@operator/runtime/logs/woody.log',
        'logVars' => [],
        'exportInterval' => 1,
    ];

}


return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => $targets,
    'flushInterval' => 1,
];