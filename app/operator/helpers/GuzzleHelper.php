<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 17.01.18
 * Time: 13:38
 */

namespace operator\helpers;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Message\ResponseInterface;

class GuzzleHelper
{
    /**
     * @param ResponseInterface $response
     * @param bool $strict
     * @return CookieJar
     */
    public static function extractCookies(ResponseInterface $response, $domain) {
        $cookies = $response->getHeader('set-cookie');
        $cookiesArray = [];
        foreach ($cookies as $cookieString) {
            $sc = SetCookie::fromString($cookieString);
            if (!$sc->getDomain()) {
                $sc->setDomain($domain);
            }
            $cookiesArray[] = $sc;
        }

        return new CookieJar(true, $cookiesArray);
    }
}