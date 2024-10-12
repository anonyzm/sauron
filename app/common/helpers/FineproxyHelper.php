<?php
namespace common\helpers;

use ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\Proxy;
use ladno\proxyconveyor\components\apiconveyor\drivers\mongodb\models\ProxyHit;
use ladno\proxyconveyor\models\Proxy as ProxyModel;
use Yii;

class FineproxyHelper
{
    public static function updateProxies($pool_id) {
        $proxyList = Yii::$app->proxyProvider->get();
        $proxies = [];
        $result = [];
        foreach ($proxyList as $key=>$proxy) {
            try {
                $proxyModel = new ProxyModel($proxy);
                $proxyMongo = new Proxy();
                $proxyMongo->setAttributes([
                    'pool_id' => $pool_id,
                    'host' => $proxyModel->host,
                    'port' => $proxyModel->port,
                    'username' => $proxyModel->username,
                    'password' => $proxyModel->password,
                ]);
                if ($proxyMongo->validate()) {
                    $proxies[] = $proxyMongo;
                    $result['done'][] = $proxyMongo->attributes;
                } else {
                    $result['errors'][] = $proxyMongo->errors;
                    \Yii::error([
                        'msg' => 'Error validating proxy',
                        'data' => json_encode($proxyMongo->attributes),
                        'errors' => json_encode($proxyMongo->errors),
                    ]);
                }
            }
            catch(\Throwable $e) {
                //echo "Wrong proxy: {$proxy} [" . $e->getMessage() . ']'.PHP_EOL;
            }
        }

        if(count($proxies) === 0) {
            \Yii::error([
                'msg' => 'Error getting proxy from provider',
                'provider' => get_class(Yii::$app->proxyProvider),
            ]);
            return;
        }

        // удаляем все старые прокси и их статистику
        Proxy::deleteAll(['pool_id' => $pool_id]);
        ProxyHit::deleteAll(['pool_id' => $pool_id]);
        // добавляем новые прокси
        foreach ($proxies as $proxy) {
            /** @var Proxy $proxy */
            $proxy->save();
        }

        return $result;
    }
}