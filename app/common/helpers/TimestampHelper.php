<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 21.08.19
 * Time: 12:39
 */

namespace common\helpers;


use DateTime;
use DateTimeZone;

class TimestampHelper
{
    public static function dayStart($timestamp, $timezone) {
        $tz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $time = strtotime(date('Y-m-d 00:00:00', $timestamp));
        date_default_timezone_set($tz);
        return $time;
    }

    public static function weekStart($timestamp, $timezone) {
        $tz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $time = strtotime('monday this week', $timestamp);
        date_default_timezone_set($tz);
        return $time;
    }

    public static function monthStart($timestamp, $timezone) {
        $tz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $time = strtotime(date('Y-m-01 00:00:00', $timestamp));
        date_default_timezone_set($tz);
        return $time;
    }

    public static function rfc3339($timestamp)
    {
        $d = new DateTime();
        $d->setTimezone(new DateTimeZone('UTC'));
        $d->setTimestamp($timestamp);

        return $d->format('Y-m-d\TH:i:s\Z');
    }
}