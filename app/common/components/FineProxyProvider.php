<?php

namespace common\components;

use common\interfaces\ProxyProviderInterface;
use yii\base\Component;

class FineProxyProvider extends Component implements ProxyProviderInterface
{
    public $login;
    public $password;

    function get() : array
    {
        $url = "http://account.fineproxy.org/api/getproxy/?format=txt&type=httpip&login={$this->login}&password={$this->password}";
        $data = file_get_contents($url);
        $data = str_replace("\r", '', $data);
        return explode("\n", $data);
    }
}