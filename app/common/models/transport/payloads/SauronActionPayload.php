<?php
namespace common\models\transport\payloads;

use ladno\woody\models\MQMessage\ActionPayload;

/**
 * Class SauronActionPayload
 */
class SauronActionPayload extends ActionPayload
{
    /**
     * @return array
     */
    public static function actions()
    {
        return [
            'twitter',
            'instagram',
            'reddit',
            'youtube',
            'youtubeUpdate',
            'presentation',
            'persist',
            'repersist',
            'representation',
            'timezone',
        ];
    }
}