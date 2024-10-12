<?php
namespace operator\components\proxy;

use operator\interfaces\ProxyInterface;
use yii\base\Component;

class Luminati extends Component implements ProxyInterface
{
    const SUPERPROXY = 'zproxy.luminati.io';
    const PORT = 22225;

    public $username;
    public $password;

    public function get() : string
    {
        $session = mt_rand();
        return "https://{$this->username}-session-{$session}:{$this->password}@".self::SUPERPROXY.':'.self::PORT;
    }
}