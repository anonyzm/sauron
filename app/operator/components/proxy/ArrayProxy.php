<?php
namespace operator\components\proxy;

use operator\interfaces\ProxyInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

class ArrayProxy extends Component implements ProxyInterface
{
    public $proxy;

    public function get() : string
    {
        if(empty($this->proxy)) {
            throw new InvalidConfigException('ArrayProxy::proxy is empty');
        }
        if(!is_array($this->proxy)) {
            throw new InvalidConfigException('ArrayProxy::proxy is not an array');
        }
        $key = array_rand($this->proxy);
        return ArrayHelper::getValue($this->proxy, $key);
    }
}