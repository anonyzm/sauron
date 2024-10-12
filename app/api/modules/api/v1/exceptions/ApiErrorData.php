<?php

namespace api\modules\api\v1\exceptions;

use yii\base\Model;

class ApiErrorData extends Model
{
    /**
     * @var string|null
     */
    public $code = null;

    public $data = [];

    public function __toString()
    {
        return $this->code . ' -> [' . print_r($this->data, 1) . ']';
    }
}
