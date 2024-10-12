<?php
namespace operator\components\proxy;

use operator\interfaces\ProxyInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class SettingsProxy extends Component implements ProxyInterface
{
    public function get() : string
    {
        $proxy = Yii::$app->settings->get('settings.defaultProxy');
        $defaultProxy = ArrayHelper::getValue(Yii::$app->params, 'settings.defaultProxy');

        if(!$proxy) {
            $proxy = $defaultProxy;
        }
        if(!$proxy) {
            throw new InvalidConfigException('SettingsProxy: no proxy set');
        }

        $proxies = explode(',', $proxy);
        $key = array_rand($proxies);
        return trim(ArrayHelper::getValue($proxies, $key));
    }
}