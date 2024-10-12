<?php

namespace common\models\transport\auth;

use yii\base\Model;

class BaseAuth extends Model
{
    const AUTH_TYPE_REDDIT = 'reddit';

    /** @var string */
    public $type;
    /** @var mixed */
    public $data;
}