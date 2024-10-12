<?php

return [
    'operatorExchange' => getenv('OPERATOR_EXCHANGE'),
    'operatorQueue' => getenv('OPERATOR_QUEUE'),
    'operatorRoutingKey' => getenv('OPERATOR_ROUTING_KEY'),

    'persisterExchange' => getenv('PERSISTER_EXCHANGE'),
    'persisterQueue' => getenv('PERSISTER_QUEUE'),
    'persisterRoutingKey' => getenv('PERSISTER_ROUTING_KEY'),

    'presentationExchange' => getenv('PRESENTATION_EXCHANGE'),
    'presentationQueue' => getenv('PRESENTATION_QUEUE'),
    'presentationRoutingKey' => getenv('PRESENTATION_ROUTING_KEY'),

    'sourcesAvailable' => [
        'twitter' => ('true' === getenv('TWITTER_ENABLED')),
        'instagram' => ('true' === getenv('INSTAGRAM_ENABLED')),
        'reddit' => ('true' === getenv('REDDIT_ENABLED')),
        'youtube' => ('true' === getenv('YOUTUBE_ENABLED')),
    ],

    'instaparser' => [
        'baseUrl' => getenv('INSTAPARSER_URL'),
        'prefer' => getenv('INSTAPARSER_PREFER'),
        'service' => getenv('INSTAPARSER_SERVICE'),
    ],

    'youtube' => [
        'key' => getenv('YOUTUBE_DEVELOPER_KEY'),
        //'apiServiceName' => getenv('YOUTUBE_API_SERVICE_NAME'),
        //'apiVersion' => getenv('YOUTUBE_API_VERSION')
    ],

    'settings' => [
        'defaultService' => getenv('DEFAULT_SERVICE_NAME'),
        'defaultServiceKey' => getenv('DEFAULT_SERVICE_KEY'),
        'defaultProxy' => getenv('PROXY_LIST'),

        'updateInstaOnDemand' => ('true' === getenv('UPDATE_INSTA_ON_DEMAND')),
        'presentationRecalculateDays' => getenv('PRESENTATION_RECALCULATE_DAYS'),
    ],

    'reddit' => [
        'id' => getenv('REDDIT_APPLICATION_ID'),
        'secret' => getenv('REDDIT_APPLICATION_SECRET'),
        'useragent' => getenv('REDDIT_APPLICATION_USERAGENT'),

        'apiUrl' => getenv('REDDIT_API_URL'),
        'authToken' => getenv('REDDIT_AUTH_TOKEN'),
    ],
];
