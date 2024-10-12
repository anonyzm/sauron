<?php
namespace operator\components\proxy;

use operator\interfaces\ProxyInterface;
use yii\base\Component;

class SuperProxy extends Component implements ProxyInterface
{
    public $url;

    public function get() : string
    {
        return (string) $this->url;
    }
}